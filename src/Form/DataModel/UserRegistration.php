<?php
namespace App\Form\DataModel;

use App\Validator\UniqueUserEmail;
use Symfony\Component\Validator\Constraints as Assert;

class UserRegistration
{
    #[UniqueUserEmail(['message' => 'Cette adresse email correspond à un compte déjà existant. Connectez-vous ou utilisez une nouvelle adresse email.'])]
    #[Assert\Email(message: 'Adresse email invalide')]
    #[Assert\NotBlank(message: 'L\'adresse email est obligatoire')]
    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    public ?string $email;

    #[Assert\EqualTo(propertyPath: 'passwordConfirm', message: '')]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(min: 6, max: 50, minMessage: '6 caractères minimum', maxMessage: '50 caractères maximum')]
    public ?string $plainPassword;

    #[Assert\EqualTo(propertyPath: 'plainPassword', message: 'Les deux mots de passe ne sont pas identiques')]
    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire')]
    public ?string $passwordConfirm = '';

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of plainPassword
     */ 
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @return  self
     */ 
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

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
}