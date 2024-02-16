<?php
namespace App\Persister;

use App\Config\SiteConfig;
use App\Entity\User;
use App\Form\DataModel\UserRegistration;
use App\Helper\FrDateTimeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserPersister 
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private FrDateTimeGenerator $dateTimeGenerator,
        private ValidatorInterface $validator,
        private TokenGeneratorInterface $tokenGenerator
    )
    {
        
    }

    public function persist(UserRegistration $userRegistration): User
    {
        $user = new User;
        $user
            ->setEmail($userRegistration->email)
            ->setPassword(
                $this->hasher->hashPassword($user, $userRegistration->plainPassword)
            )
            ->setRoles([SiteConfig::ROLE_USER])
            ->setCreatedAt($this->dateTimeGenerator->generateImmutable())
            ;
        
        $violations = $this->validator->validate($user);
        if(count($violations) !== 0)
        {
            $messages = [];
            /** @var ConstraintViolation $violation */
            foreach($violations as $violation)
            {
                $messages[] = $violation->getPropertyPath() . ' => ' . $violation->getMessage();
            }
            throw new Exception('Il y a des erreurs de validation dans User au moment de le persister : '. implode(', ', $messages));
        }

        /** pour la vérification de l'email */
        $user->setConfirmationToken(
            $this->tokenGenerator->generateToken()
        )
        ->setConfirmationTokenExpireAt(
            time() + SiteConfig::TOKEN_TIME_VALIDITY
        );

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}