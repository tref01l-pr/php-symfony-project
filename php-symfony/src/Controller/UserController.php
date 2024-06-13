<?php

namespace App\Controller;

use App\Dto\RegistrationUserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id?}', name: 'app_user')]
    public function index($id = null): Response
    {
        return $this->render('user/user-info.html.twig', [
            'controller_name' => 'UserController',
            'id' => $id,
        ]);
    }
}
