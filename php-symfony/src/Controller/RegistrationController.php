<?php

namespace App\Controller;

use App\Contracts\RegistrationUserRequest;
use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registration')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $registrationUserRequest = new RegistrationUserRequest();
        $form = $this->createForm(UserType::class, $registrationUserRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationUserRequest = $form->getData();

            $user = new User();
            $user->setEmail($registrationUserRequest->getEmail());
            $user->setRoles(['ROLE_USER']);
            $user->setUsername($registrationUserRequest->getUsername());

            $plaintextPassword = $registrationUserRequest->getPassword();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Registration successful!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/user-registration.html.twig', [
            'controller_name' => 'RegistrationController',
            'registration_form' => $form->createView(),
        ]);
    }
}