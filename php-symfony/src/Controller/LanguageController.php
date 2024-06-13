<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\LocaleSwitcher;

class LanguageController extends AbstractController
{
    #[Route('/change-locale/{_locale}', name: 'change_locale')]
    public function changeLocale(Request $request, $_locale, LocaleSwitcher $localeSwitcher): Response
    {
        $localeSwitcher->setLocale($_locale);

        $request->getSession()->set('_locale', $_locale);

        return $this->redirect($request->headers->get('referer', $this->generateUrl('app_homepage')));
    }
}