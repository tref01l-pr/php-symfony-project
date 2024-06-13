<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    #[Route('/conference', name: 'app_conference')]
    public function index(): Response
    {
        $controllerName = 'ConferenceController';

        $content = $this->renderView('conference/user-info.html.twig', [
            'controller_name' => $controllerName,
        ]);

        return new Response($content);
    }
}
