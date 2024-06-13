<?php

namespace App\Controller;

use App\Contracts\CreateProductRequest;
use App\Contracts\FilterRequest;
use App\Dto\RegistrationUserDto;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\FilterType;
use App\Form\Type\ProductType;
use App\Form\Type\UserType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class StoreController extends AbstractController
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    const DEFAULT_LIMIT = 10;
    #[Route('/products-list/{page}', name: 'app_products_list')]
    public function productsList(
        PaginatorInterface $paginator,
        Request $request,
        ProductRepository $productRepository,
        $page = 1): Response
    {
        $filterRequest = new FilterRequest();
        $form = $this->createForm(FilterType::class, $filterRequest);
        $form->handleRequest($request);
        $category = $request->query->getInt('category', -1);;
        $minValue = $request->query->getInt('minValue', -1);;
        $maxValue = $request->query->getInt('maxValue', -1);;

        if($category == -1 && $minValue == -1 && $maxValue == -1){
            $query = $productRepository->createQueryBuilder('u')
                ->orderBy('u.category', 'ASC')
                ->addOrderBy('u.price', 'DESC')
                ->getQuery();

            $request->query->getInt('page', 1);
            $pageSize = 10;
            $pagination = $paginator->paginate(
                $query,
                $page,
                $pageSize
            );
        }
        else{
            $queryBuilder = $productRepository->createQueryBuilder('u');

            if ($category != -1) {
                $queryBuilder->andWhere('u.category = :category')
                    ->setParameter('category', $category);
            }

            if ($minValue != -1) {
                $queryBuilder->andWhere('u.price >= :minValue')
                    ->setParameter('minValue', $minValue);
            }

            if ($maxValue != -1) {
                $queryBuilder->andWhere('u.price <= :maxValue')
                    ->setParameter('maxValue', $maxValue);
            }

            $queryBuilder->orderBy('u.category', 'ASC')
                ->addOrderBy('u.price', 'DESC');

            $query = $queryBuilder->getQuery();

            $request->query->getInt('page', 1);
            $pageSize = 10;
            $pagination = $paginator->paginate(
                $query,
                $page,
                $pageSize
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $filterRequest = $form->getData();

            $category = $filterRequest->getCategory()->getId();
            $minValue = $filterRequest->getMinPrice();
            $maxValue = $filterRequest->getMaxPrice();

            if ($minValue >= $maxValue || $minValue <= 0 || $maxValue <= 0)
            {
                $minValue = -1;
                $maxValue = -1;
            }

            return $this->redirect($this->generateUrl('app_products_list' , ['page' => 1, 'category' => $category, 'minValue' => $minValue, 'maxValue' => $maxValue]));
        }



        return $this->render('store/products-list.html.twig', [
            'pagination' => $pagination,
            'filter_form' => $form,
        ]);
    }

    #[Route('/product-info/{id}', name: 'app_product_info')]
    public function productInfo(ProductRepository $productRepository, CategoryRepository $categoryRepository, $id = null): Response
    {
        if ($id == null || !is_numeric($id) || $id < 0) {
            return $this->render('store/product-info.html.twig', [
                'product' => null
            ]);
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return $this->render('store/product-info.html.twig', [
                'product' => null
            ]);
        }

        $category = $categoryRepository->find($product->getCategory());

        return $this->render('store/product-info.html.twig', [
            'product' => $product,
            'category' => $category
        ]);
    }

    #[Route('/add-product-to-cart', name: 'app_add_product_to_cart')]
    public function AddProductToCart(Request $request, ProductRepository $productRepository): Response
    {
        $productId = $request->query->get('id');

        if ($productId == null || !is_numeric($productId) || $productId < 0) {
            return $this->redirect($this->generateUrl('app_product_info', ['id' => $productId]));
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->redirect($this->generateUrl('app_product_info', ['id' => $productId]));
        }

        $products = $this->session->get('products', []);
        if ($products == null) {
            $products = [];
        }

        $newProduct = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'number' => 1,
        ];
        $products[] = $newProduct;

        $this->session->set('products', $products);
        return $this->redirect($this->generateUrl('app_products_list'));
    }

    #[Route('/remove-product-from-cart', name: 'app_remove_product_from_cart')]
    public function RemoveProductFromCart(Request $request): Response
    {
        $productNumber = $request->query->get('number');
        if ($productNumber == null || !is_numeric($productNumber) || $productNumber < 0) {
            return $this->render('store/product-info.html.twig', [
                'product' => null
            ]);
        }

        $products = $this->session->get('products', []);

        if (isset($products[$productNumber - 1])) {
            unset($products[$productNumber - 1]);
        }


        $this->session->set('products', $products);
        return $this->redirect($this->generateUrl('app_show_all_saved_products'));
    }

    #[Route('/show-all-saved-products', name: 'app_show_all_saved_products')]
    public function showCart(ProductRepository $productRepository, $productId = null): Response
    {
        $products = $this->session->get('products', []);

        return $this->render('store/cart.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $products = $this->session->get('products', []);
        if ($products == null || count($products) == 0) {
            $this->redirect($this->generateUrl('app_show_all_saved_products'));
        }

        $user = $this->getUser();
        if ($user == null) {
            $this->redirect($this->generateUrl('app_login'));
        }

        foreach ($products as $product) {
            $productEntity = $productRepository->find($product['id']);
            if ($productEntity == null) {
                $product = new Product();
                $product->setName('Product not found');
                $order = new Order();
                $order->setProduct($product);
                continue;
            }

            $order = new Order();
            $order->setProduct($productEntity);
            $order->setUser($user);
            $order->setDate(new \DateTime());
            $order->setCount(1);
            $entityManager->persist($order);
        }
        $entityManager->flush();

        $this->session->set('products', []);
        return $this->redirect($this->generateUrl('app_products_list'));

        /*return $this->render('store/cart.html.twig', [
            'products' => $products,
            'info' => [
                'products' => $products,
                'orders' => $orders,
                'productEntities' => $productEntities,
                '$productEntitiesFindProductId'=> $productEntitiesFindProductId
            ]
        ]);*/
    }
}