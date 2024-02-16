<?php
namespace App\Form\DataModel;

use Symfony\Component\Validator\Constraints as Assert;

class NewPassword
{
    #[Assert\EqualTo(propertyPath: 'passwordConfirm', message: '')]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(min: 6, max: 50, minMessage: '6 caractères minimum', maxMessage: '50 caractères maximum')]
    public ?string $password;

    #[Assert\EqualTo(propertyPath: 'password', message: 'Les deux mots de passe ne sont pas identiques')]
    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire')]
    public ?string $passwordConfirm = '';

    /**
     * Get the value of passwordConfirm
     */ 
    public function getPasswordConfirm()
    {
        return $this->passwordConfirm;
    }

    /**
     * Set the value of passwordConfirm
     *
     * @return  self
     */ 
    public function setPasswordConfirm($passwordConfirm)
    {
        $this->passwordConfirm = $passwordConfirm;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}