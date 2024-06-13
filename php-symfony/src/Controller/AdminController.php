<?php
namespace App\Controller;

use App\Contracts\CreateCategoryRequest;
use App\Contracts\CreateProductRequest;
use App\Contracts\RegistrationUserRequest;
use App\Dto\RegistrationUserDto;
use App\Dto\UserDto;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Form\Type\CategoryType;
use App\Form\Type\ProductType;
use App\Form\Type\UserType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/user-info/{id?}', name: 'app_admin_user_info')]
    public function getUserInfo(UserRepository $userRepository, OrderRepository $orderRepository, $id = null): Response
    {
        if ($id == null || !is_numeric($id) || $id < 0) {
            return $this->render('admin/user-info.html.twig', [
                'controller_name' => 'AdminController',
                'user' => null
            ]);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->render('admin/user-info.html.twig', [
                'controller_name' => 'AdminController',
                'user' => null,
                'orders' => null
            ]);
        }

        $orders = $orderRepository->findOrdersByUserId($user->getId());

        $userDto = new UserDto($user);

        return $this->render('admin/user-info.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $userDto,
            'orders' => $orders
        ]);
    }

    #[Route('/admin/block-user/{id?}', name: 'app_admin_block_user')]
    public function blockUser(UserRepository $userRepository, EntityManagerInterface $entityManager, $id = null): Response
    {
        if ($id == null || !is_numeric($id) || $id < 0) {
            return $this->redirect($this->generateUrl('app_admin_user_info', ['id' => $id]));
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->redirect($this->generateUrl('app_admin_user_info', ['id' => $id]));
        }

        $user->setIsblocked(!$user->isIsblocked());
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirect($this->generateUrl('app_admin_user_info', ['id' => $id]));
    }

    #[Route('/admin/create-user', name: 'app_admin_create_user')]
    public function createUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        dump("createUser");
        $registrationUserRequest = new RegistrationUserRequest();
        $form = $this->createForm(UserType::class, $registrationUserRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $registrationUserRequest = $form->getData();
            dump($registrationUserRequest);
            var_dump($registrationUserRequest->getUsername());
            $user = new User();
            $user->setUsername($registrationUserRequest->getUsername());
            $user->setEmail($registrationUserRequest->getEmail());

            $user->setRoles(['ROLE_USER']);

            $plaintextPassword = $registrationUserRequest->getPassword();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('admin/create-user.html.twig', [
            'controller_name' => 'AdminController',
            'registration_form' => $form,
        ]);
    }

    #[Route('/admin/all-users', name: 'app_admin_all_users')]
    public function showAllUsers(LoggerInterface $logger, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        $logger->info('All users were requested' . count($users));
        $userDTOs = [];
        foreach ($users as $user) {
            $userDTOs[] = new UserDto($user);
        }

        return $this->render('admin/all-users.html.twig', [
            'controller_name' => 'AdminController',
            'users' => $userDTOs,
        ]);
    }

    #[Route('/admin/create-category', name: 'app_admin_create_category')]
    public function createCategory(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $createCategoryRequest = new CreateCategoryRequest();
        $form = $this->createForm(CategoryType::class, $createCategoryRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createCategoryRequest = $form->getData();

            if (!$createCategoryRequest->getName()) {
                $form->addError(new FormError('Category name is required.'));
            }
            elseif ($categoryRepository->findOneBy(['name' => $createCategoryRequest->getName()])) {
                $form->addError(new FormError('Category with this name already exists.'));
            }
            else {
                $category = new Category();
                $category->setName($createCategoryRequest->getName());

                $entityManager->persist($category);
                $entityManager->flush();
                return $this->redirectToRoute('app_admin');
            }

        }

        return $this->render('admin/create-category.html.twig', [
            'category_form' => $form,
        ]);
    }

    #[Route('/admin/create-product', name: 'app_admin_create_product')]
    public function createProduct(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $createProductRequest = new CreateProductRequest();
        $form = $this->createForm(ProductType::class, $createProductRequest);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $containErrors = false;
            $createProductRequest = $form->getData();
            if (!$createProductRequest->getName()) {
                $form->addError(new FormError('Product name is required.'));
                $containErrors = true;
            }
            elseif ($productRepository->findOneBy(['name' => $createProductRequest->getName()])) {
                $form->addError(new FormError('Product with this name already exists.'));
                $containErrors = true;
            }

            if (!$createProductRequest->getCategory()) {
                $form->addError(new FormError('Category is required.'));
                $containErrors = true;
            }

            if (!$createProductRequest->getPrice()) {
                $form->addError(new FormError('Product price is required.'));
                $containErrors = true;
            }
            elseif ($createProductRequest->getPrice() <= 0) {
                $form->addError(new FormError('Product price must be a positive number.'));
                $containErrors = true;
            }

            if (!$containErrors) {
                $category = $categoryRepository->findOneBy(['name' => $createProductRequest->getCategory()->getName()]);
                if ($category) {
                    $product = new Product();
                    $product->setName($createProductRequest->getName());
                    $product->setCategory($category);
                    $product->setPrice($createProductRequest->getPrice());
                    $product->setDescription($createProductRequest->getDescription());

                    $entityManager->persist($product);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_admin');
                }
                else {
                    $form->addError(new FormError('Category not found.'));
                }
            }

        }

        return $this->render('admin/create-product.html.twig', [
            'product_form' => $form,
        ]);
    }
}
