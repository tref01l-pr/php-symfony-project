<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class UserInteractionController extends AbstractController
{
    #[Route('/database-interaction/create-user', name: 'create-user', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $requestData = json_decode($request->getContent(), true);

        /*$user = new User();
        $user->setUsername($requestData['username']);
        $user->setEmail($requestData['email']);
        $user->setPassword($requestData['password']);
        $user->setFirstName($requestData['firstName']);
        $user->setLastName($requestData['lastName']);
        $user->setDetails($requestData['details']);

        $entityManager->persist($user);
        $entityManager->flush();*/


        return $this->json([
            'message' => 'User created',
            //'userId' => $user->getId(),
        ]);
    }

    /*#[Route('/database-interaction/get-all-users', name: 'get-all-users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        if (!$users) {
            return new Response('No users found', Response::HTTP_NOT_FOUND);
        }

        return $this->json($users, Response::HTTP_OK);
    }*/
}