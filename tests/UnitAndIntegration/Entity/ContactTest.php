<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Contact;
use App\Config\TextConfig;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Entity
 */
class ContactTest extends EntityTest
{
    public function testValidContact()
    {
        $this->assertHasErrors(0, $this->createValidContact());
    }

    public function testValidNoUser()
    {
        $this->assertHasErrors(
            0,
            $this->createValidContact()->setUser(null)
        );
    }
    public function testInvalidCivility()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setCivility('')
        );
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setCivility('Autre chose que monsieur ou madame')
        );
    }
    public function testInvalidBlankFirstName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setFirstName('')
        );
    }
    public function testInvalidTooLongFirstName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setFirstName($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankLastName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setLastName('')
        );
    }
    public function testInvalidTooLongLastName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setLastName($this->moreThan200Caracters)
        );
    }
    public function testNotBlankEmail()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setEmail('')
        );
    }
    public function testInvalidEmail()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setEmail('invalideemail.fr')
        );
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setEmail('invalide@email.')
        );
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setEmail('invalideemail')
        );
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setEmail('invalideemail@mail')
        );
    }
    public function testInvalidTooLongEmail()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setEmail('email'.str_repeat('0123456789', 20).'@gmail.com')
        );
    }
    public function testValidNullPhone()
    {
        $this->assertHasErrors(
            0,
            $this->createValidContact()->setPhone(null)
        );
        $this->assertHasErrors(
            0,
            $this->createValidContact()->setPhone('')
        );
    }
    public function testInvalidTooLongPhone()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setPhone('0123456789012345678901234567890') // 31 caracters
        );
    }
    public function testInvalidBlankMessage()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setMessage('')
        );
    }
    public function testValidMediumLongMessage()
    {
        $this->assertHasErrors(
            0,
            $this->createValidContact()->setMessage(
                $this->moreThan200Caracters . ' more than 200 caracters'
            )
        );
    }
    public function testInvalidTooLongMessage()
    {
        $this->assertHasErrors(
            1,
            $this->createValidContact()->setMessage($this->moreThan2000Caracters)
        );
    }

    private function createValidContact(): Contact
    {
        return (new Contact)
                ->setUser(new User)
                ->setCivility(TextConfig::CIVILITY_M)
                ->setFirstName('jean')
                ->setLastName('delafontaine')
                ->setEmail('jean@delafontaine.fr')
                ->setPhone('0601020304')
                ->setMessage('voici mon court message valide')
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}