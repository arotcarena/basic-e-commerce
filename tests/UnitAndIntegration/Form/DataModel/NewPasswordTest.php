<?php
namespace App\Tests\UnitAndIntegration\Form\DataModel;

use App\Form\DataModel\NewPassword;
use App\Tests\UnitAndIntegration\Entity\EntityTest;

/**
 * @group Form
 */
class NewPasswordTest extends EntityTest
{
    public function testValidNewPassword()
    {
        $this->assertHasErrors(0, $this->createValidNewPassword());
    }
        
    public function testInvalidBlankPassword()
    {
        $this->assertHasErrors(
            3, 
            $this->createValidNewPassword()->setPassword('')->setPasswordConfirm('')
        );
    }
    public function testInvalidPasswordLength()
    {
        $this->assertHasErrors(
            1,
            $this->createValidNewPassword()->setPassword('passe')->setPasswordConfirm('passe') // < 6 caractères
        );
        $this->assertHasErrors(
            1,
            $this->createValidNewPassword()->setPassword($this->moreThan50Caracters)->setPasswordConfirm($this->moreThan50Caracters)
        );
    }
    public function testInvalidDifferentPasswords()
    {
        $this->assertHasErrors(
            2,
            $this->createValidNewPassword()->setPasswordConfirm('otherpassword')
        );
    }

    private function createValidNewPassword(): NewPassword
    {
        return (new NewPassword)
                ->setPassword('password')
                ->setPasswordConfirm('password')
                ;
    }
}