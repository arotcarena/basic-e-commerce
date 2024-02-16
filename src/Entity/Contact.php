<?php

namespace App\Entity;

use App\Config\TextConfig;
use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[assert\Choice(choices: [TextConfig::CIVILITY_F, TextConfig::CIVILITY_M], message: 'Veuillez choisir "'.TextConfig::CIVILITY_F.'" ou "'.TextConfig::CIVILITY_M.'"')]
    #[ORM\Column(length: 255)]
    private ?string $civility = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[Assert\Email(message: 'Adresse email invalide')]
    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'L\'adresse email est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Assert\Length(max: 30, maxMessage: '30 caractères maximum')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[Assert\Length(max: 2000, maxMessage: '2000 caractères maximum')]
    #[Assert\NotBlank(message: 'Le message est obligatoire')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
