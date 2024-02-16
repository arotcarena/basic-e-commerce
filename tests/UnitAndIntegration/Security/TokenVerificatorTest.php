<?php
namespace App\Tests\UnitAndIntegration\Security;

use App\Entity\User;
use App\Security\TokenVerificator;
use App\Tests\Utils\UserFixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group Security
 */
class TokenVerificatorTest extends KernelTestCase
{
    use UserFixturesTrait;

    private TokenVerificator $tokenVerificator;

    public function setUp(): void 
    {
        parent::setUp();

        $this->tokenVerificator = static::getContainer()->get(TokenVerificator::class);
    }

    public function testInvalidTokenAndIncorrectId()
    {
        $this->assertNull(
            $this->tokenVerificator->resolveUser('invalid_token', 'confirmationToken')
        );
        $this->assertNull(
            $this->tokenVerificator->resolveUser('invalid_token', 'resetPasswordToken')
        );
    }
    public function testInvalidTokenButCorrectId()
    {
        $user = $this->getUserWithValidToken();

        $fullToken = $user->getId() . '==invalid_token';
        $this->assertNull(
            $this->tokenVerificator->resolveUser($fullToken, 'confirmationToken')
        );
        $this->assertNull(
            $this->tokenVerificator->resolveUser($fullToken, 'resetPasswordToken')
        );
    }
    public function testExpiredToken()
    {
        $user = $this->getUserWithExpiredToken();

        $fullToken = $user->getId() . '=='. $user->getConfirmationToken();
        $this->assertNull(
            $this->tokenVerificator->resolveUser($fullToken, 'confirmationToken')
        );
        
        $fullToken = $user->getId() . '=='. $user->getResetPasswordToken();
        $this->assertNull(
            $this->tokenVerificator->resolveUser($fullToken, 'resetPasswordToken')
        );
    }
    
    // parfois ce test échoue car le tokenExpireAt est créé avec une valeur inférieure à time (pourquoi ??? aucune idée mais ça revient tout seul à la normale)
    public function testValidToken()
    {
        $user = $this->getUserWithValidToken();
        $fullToken = $user->getId() . '=='. $user->getConfirmationToken();
        $this->assertInstanceOf(
            User::class,
            $this->tokenVerificator->resolveUser($fullToken, 'confirmationToken')
        );
        
        $fullToken = $user->getId() . '=='. $user->getResetPasswordToken();
        $this->assertInstanceOf(
            User::class,
            $this->tokenVerificator->resolveUser($fullToken, 'resetPasswordToken')
        );
    }

}