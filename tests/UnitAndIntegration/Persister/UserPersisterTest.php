<?php
namespace App\Tests\UnitAndIntegration\Persister;

use App\Form\DataModel\UserRegistration;
use App\Persister\UserPersister;
use Exception;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @group Persister
 */
class UserPersisterTest extends KernelTestCase
{
    private UserPersister $userPersister;

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        
        $this->userPersister = static::getContainer()->get(UserPersister::class);
    }

    public function testInvalidInputResultsInThrowingException()
    {
        $userRegistration = (new UserRegistration)
                            ->setEmail('invalidemail.fr')
                            ->setPlainPassword('pass')
                            ->setPasswordConfirm('pass')
                            ;
        $this->expectException(Exception::class);
        $this->userPersister->persist($userRegistration);
    }
    public function testEmailIsSetCorrectly()
    {
        $userRegistration = $this->createValidUserRegistration();
        $user = $this->userPersister->persist($userRegistration);

        $this->assertEquals(
            $userRegistration->getEmail(), 
            $user->getEmail()
        );
    }
    public function testPasswordIsHashedCorrectly()
    {
        $userRegistration = $this->createValidUserRegistration();
        $user = $this->userPersister->persist($userRegistration);
        
        /** @var UserPasswordHasherInterface */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->assertTrue(
            $hasher->isPasswordValid($user, $userRegistration->getPlainPassword())
        );
    }
    public function testConfirmationTokenIsSet()
    {
        $user = $this->userPersister->persist($this->createValidUserRegistration());
        $this->assertNotNull($user->getConfirmationToken());
    }
    public function testConfirmationTokenExpireAtIsSet()
    {
        $user = $this->userPersister->persist($this->createValidUserRegistration());
        $this->assertNotNull($user->getConfirmationTokenExpireAt());
    }
    public function testUserIsValid()
    {
        $user = $this->userPersister->persist($this->createValidUserRegistration());
        /** @var ValidatorInterface */
        $validator = static::getContainer()->get(ValidatorInterface::class);
        $violations = $validator->validate($user);
        $this->assertCount(0, $violations);
    }

    private function createValidUserRegistration()
    {
        $faker = Factory::create();
        
        return (new UserRegistration)
                ->setEmail($faker->email())
                ->setPlainPassword('password')
                ->setPasswordConfirm('password')
                ;
    }
}