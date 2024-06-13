<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $event->getRequest()->getSession()->getFlashBag()->add('success', 'You have been successfully logged out.');
    }
}