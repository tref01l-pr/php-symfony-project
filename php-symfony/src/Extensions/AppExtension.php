<?php

namespace App\Extensions;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $cache;
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->cache = new FilesystemAdapter();
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function getGlobals(): array
    {
        return [
            'cart_count' => $this->getCartCount(),
        ];
    }

    private function getCartCount(): int
    {
        $user = $this->security->getUser();
        if (!$user) {
            return 0; // Если пользователь не аутентифицирован, возвращаем 0
        }



        return $this->cache->get('cart_count', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            // Логика для получения количества товаров в корзине
            // Например, из базы данных или другой системы
            return 5; // Допустим, у нас 5 товаров в корзине
        });
    }
}