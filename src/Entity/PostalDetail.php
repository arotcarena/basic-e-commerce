<?php

namespace App\Entity;

use App\Config\TextConfig;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostalDetailRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostalDetailRepository::class)]
class PostalDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Choice(choices: [TextConfig::CIVILITY_F, TextConfig::CIVILITY_M], message: 'Vous devez choisir "'.TextConfig::CIVILITY_F.'" ou "'.TextConfig::CIVILITY_M.'"')]
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

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'L\'adresse ligne 1 est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $lineOne = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lineTwo = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $postcode = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'Le pays est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getLineOne(): ?string
    {
        return $this->lineOne;
    }

    public function setLineOne(string $lineOne): self
    {
        $this->lineOne = $lineOne;

        return $this;
    }

    public function getLineTwo(): ?string
    {
        return $this->lineTwo;
    }

    public function setLineTwo(?string $lineTwo): self
    {
        $this->lineTwo = $lineTwo;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

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
