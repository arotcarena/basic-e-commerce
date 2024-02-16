<?php

namespace App\Controller\Tests;

use App\Service\CartService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Ce controller sert uniquement pour le test CartServiceTest  et le CartOrderImportantTest
 */
class CartServiceTestController extends AbstractController
{
    public function __construct(
        private CartService $cartService
    )
    {

    }

    /**
     * Sert à tester le nb de database queries de la function onLoginUpdate du CartService
     *
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/tests/cartService/onLoginUpdate', name: 'tests_cartService_onLoginUpdate')]
    public function onLoginUpdate(): Response
    {
        $this->cartService->onLoginUpdate($this->getUser());
        return new Response('');
    }

    
    #[IsGranted('ROLE_USER')]
    #[Route('/tests/cartService/empty', name: 'tests_cartService_empty')]
    public function empty(): Response
    {
        $this->cartService->empty();
        return new Response('');
    }

}