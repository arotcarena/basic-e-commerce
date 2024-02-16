<?php
namespace App\Tests\UnitAndIntegration\Form\DataModel;

use App\Repository\UserRepository;
use App\Form\DataModel\UserRegistration;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\UnitAndIntegration\Entity\EntityTest;
use App\Tests\Utils\FixturesTrait;
use App\Tests\Utils\UserFixturesTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

/**
 * @group Form
 */
class UserRegistrationTest extends EntityTest
{
    use FixturesTrait;

    use UserFixturesTrait;

    public function testValidUserRegistration()
    {
        $this->assertHasErrors(0, $this->createValidUserRegistration());
    }
    public function testInvalidExistingEmail()
    {
        $this->loadFixtures([UserTestFixtures::class]);
        $existingEmail = $this->findUser([])->getEmail();

        $this->assertHasErrors(
            1,
            $this->createValidUserRegistration()->setEmail($existingEmail)
        );
    }
    public function testInvalidBlankEmail()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUserRegistration()->setEmail('')
        );
    }
    public function testInvalidEmail()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUserRegistration()->setEmail('invalide@mail.')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidUserRegistration()->setEmail('invalidemail')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidUserRegistration()->setEmail('invalidemail.fr')
        );
    }
    public function testInvalidTooLongEmail()
    {
        $this->assertHasErrors(
            1,
            $this->createValidUserRegistration()->setEmail(str_repeat('email12345', 20) . '@gmail.com')  // more than 200 caracters
        );
    }
    public function testInvalidBlankPassword()
    {
        $this->assertHasErrors(
            3, 
            $this->createValidUserRegistration()->setPlainPassword('')->setPasswordConfirm('')
        );
    }
    public function testInvalidPasswordLength()
    {
        $this->assertHasErrors(
            1,
            $this->createValidUserRegistration()->setPlainPassword('passe')->setPasswordConfirm('passe') // 4 caractères
        );
        $this->assertHasErrors(
            1,
            $this->createValidUserRegistration()->setPlainPassword($this->moreThan50Caracters)->setPasswordConfirm($this->moreThan50Caracters)
        );
    }
    public function testInvalidDifferentPasswords()
    {
        $this->assertHasErrors(
            2,
            $this->createValidUserRegistration()->setPasswordConfirm('otherpassword')
        );
    }

    private function createValidUserRegistration(): UserRegistration
    {
        return (new UserRegistration)
                ->setEmail('validemail@gmail.com')
                ->setPlainPassword('password')
                ->setPasswordConfirm('password')
                ;
    }
}