<?php
namespace App\Tests\UnitAndIntegration\Entity;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Address;
use App\Config\TextConfig;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Entity
 */
class AddressTest extends EntityTest
{
    public function testValidAddress()
    {
        $this->assertHasErrors(0, $this->createValidAddress());
    }

    public function testInvalidNoUser()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setUser(null)
        );
    }
    public function testInvalidCivility()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setCivility('')
        );
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setCivility('Autre chose que monsieur ou madame')
        );
    }
    public function testInvalidBlankFirstName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setFirstName('')
        );
    }
    public function testInvalidTooLongFirstName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setFirstName($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankLastName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setLastName('')
        );
    }
    public function testInvalidTooLongLastName()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setLastName($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankLineOne()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setLineOne('')
        );
    }
    public function testInvalidTooLongLineOne()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setLineOne($this->moreThan200Caracters)
        );
    }
    public function testValidBlankLineTwo()
    {
        $this->assertHasErrors(
            0,
            $this->createValidAddress()->setLineTwo('')
        );
    }
    public function testInvalidTooLongLineTwo()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setLineTwo($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankCity()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setCity('')
        );
    }
    public function testInvalidTooLongCity()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setCity($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankPostcode()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setPostcode('')
        );
    }
    public function testInvalidTooLongPostcode()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setPostcode($this->moreThan200Caracters)
        );
    }
    public function testInvalidBlankCountry()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setCountry('')
        );
    }
    public function testInvalidTooLongCountry()
    {
        $this->assertHasErrors(
            1,
            $this->createValidAddress()->setCountry($this->moreThan200Caracters)
        );
    }

    private function createValidAddress(): Address
    {
        return (new Address)
                ->setUser(new User)
                ->setCivility(TextConfig::CIVILITY_M)
                ->setFirstName('jean')
                ->setLastName('Delafontaine')
                ->setLineOne('22 route de la mer')
                ->setLineTwo('résidence des fleurs')
                ->setCity('Marseille')
                ->setPostcode('13000')
                ->setCountry('France')
                ->setCreatedAt(new DateTimeImmutable())
                ;
    }
}