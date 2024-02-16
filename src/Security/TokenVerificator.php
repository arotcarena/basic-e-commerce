<?php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class TokenVerificator
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    )
    {

    }
 
    public function resolveUser(string $fullToken, string $tokenName): ?User
    {
        try 
        {
            $id = explode('==', $fullToken)[0];
            $token = explode('==', $fullToken)[1];
            $user = $this->userRepository->find($id);
            if($user === null)
            {
                return null;
            }
            $getToken = 'get'. ucfirst($tokenName);
            $getTokenExpireAt = 'get' . ucfirst($tokenName) . 'ExpireAt';
            
            // parfois dans les tests le tokenExpireAt est créé avec une valeur inférieure à time ce qui fait échouer le test  (pourquoi ??? aucune idée)

            if(
                $user->$getToken() === $token && 
                $user->$getTokenExpireAt() > time()
                )
            {
                return $user;
            }
        }
        catch(Exception $e)
        {
            //
        }
        return null;
    }
}