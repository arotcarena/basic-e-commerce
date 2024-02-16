<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\User;
use DateTimeImmutable;
use App\Config\SiteConfig;
use App\Config\TextConfig;
use App\DataFixtures\Tests\UserTestFixtures;
use App\Tests\UnitAndIntegration\Entity\EntityTest;
use App\Tests\Utils\FixturesTrait;
use App\Tests\Utils\UserFixturesTrait;

/**
 * @group Entity
 */
class UserTest extends EntityTest
{
    use FixturesTrait;

    use UserFixturesTrait;


    public function testValidUser()
    {
        $this->assertHasErrors(0, $this->createValidUser());
    }
    public function testInvalidExistingEmail()
    {
        $this->loadFixtures([UserTestFixtures::class]);
        $existingEmail = $this->findUser([])->getEmail();

        $this->assertHasErrors(
            1,
            $this->createValidUser()->setEmail($existingEmail)
        );
    }
    public function testInvalidEmail()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setEmail('invalide@mail.')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setEmail('invalidemail')
        );
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setEmail('invalidemail.fr')
        );
    }
    public function testInvalidCivility()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setCivility('autre chose que monsieur ou madame ou vide')
        );
    }
    public function testInvalidRoles()
    {
        $this->assertHasErrors(
            3, 
            $this->createValidUser()->setRoles([24, 48, 50])
        );
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setRoles([])
        );
    }
    public function testInvalidBlankEmail()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setEmail('')
        );
    }
    public function testValidBlankCivility()
    {
        $this->assertHasErrors(
            0, 
            $this->createValidUser()->setCivility('')
        );
    }
    public function testValidBlankFirstName()
    {
        $this->assertHasErrors(
            0, 
            $this->createValidUser()->setFirstName('')
        );
    }
    public function testValidBlankLastName()
    {
        $this->assertHasErrors(
            0, 
            $this->createValidUser()->setLastName('')
        );
    }
    public function testInvalidTooLongEmail()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setEmail('adresseemailvalidemaistroplongue'.str_repeat('0123456789', 20).'@gmail.com')
        );
    }
    public function testInvalidTooLongFirstName()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setFirstName($this->moreThan200Caracters)
        );
    }
    public function testInvalidTooLongLastName()
    {
        $this->assertHasErrors(
            1, 
            $this->createValidUser()->setLastName($this->moreThan200Caracters)
        );
    }
    

    private function createValidUser(): User
    {
        return (new User)
                ->setEmail('emailvalide@gmail.com')
                ->setPassword('passwordvalide')
                ->setRoles([SiteConfig::ROLE_USER, SiteConfig::ROLE_ADMIN])
                ->setCivility(TextConfig::CIVILITY_M)
                ->setFirstName('prénom')
                ->setLastName('nom')
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}