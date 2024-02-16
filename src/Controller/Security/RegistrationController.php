<?php

namespace App\Controller\Security;

use App\Config\TextConfig;
use App\Persister\UserPersister;
use App\Form\UserRegistrationType;
use App\Security\TokenVerificator;
use App\Form\DataModel\UserRegistration;
use Doctrine\ORM\EntityManagerInterface;
use App\Email\Security\ConfirmationEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    public function __construct(
        private UserPersister $userPersister,
        private ConfirmationEmail $confirmationEmail,
        private EntityManagerInterface $em,
        private TokenVerificator $tokenVerificator
    )
    {

    }


    #[Route('/créer-un-compte', name: 'security_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }
        
        $userRegistration = new UserRegistration;
        $form = $this->createForm(UserRegistrationType::class, $userRegistration);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) 
        {
            $user = $this->userPersister->persist($userRegistration);
            $this->confirmationEmail->send($user);
            $this->addFlash('success', TextConfig::ALERT_REGISTER_SUCCESS);
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/activer-mon-compte', name: 'security_emailConfirmation')]
    public function emailConfirmation(Request $request): Response
    {
        $user = $this->tokenVerificator->resolveUser($request->query->get('token'), 'confirmationToken');
        if($user)
        {
            $user->setConfirmed(true)
                ->setConfirmationToken(null)
                ->setConfirmationTokenExpireAt(null)
                ;
            $this->em->flush();
            $this->addFlash('success', TextConfig::ALERT_CONFIRMATION_SUCCESS);
            return $this->redirectToRoute('security_login');
        }
        $this->addFlash('danger', 'Le lien est invalide ou périmé');
        return $this->redirectToRoute('home');
    }
}
