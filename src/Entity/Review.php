<?php

namespace App\Entity;

use App\Config\SiteConfig;
use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[Assert\NotNull(message: 'Veuillez choisir une note entre 1 et 5')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Veuillez choisir une note entre 1 et 5')]
    #[ORM\Column]
    private ?int $rate = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $moderationStatus = null;

    public function isStrong(): bool 
    {
        return $this->moderationStatus === null;
    }

    public function getModerationStatusLabel(): string
    {
        switch($this->moderationStatus)
        {
            case SiteConfig::MODERATION_STATUS_ACCEPTED:
                return SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL;
                break;
            case SiteConfig::MODERATION_STATUS_REFUSED:
                return SiteConfig::MODERATION_STATUS_REFUSED_LABEL;
            default:
                return SiteConfig::MODERATION_STATUS_PENDING_LABEL;
        }
    }

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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

    public function getModerationStatus(): ?string
    {
        return $this->moderationStatus;
    }

    public function setModerationStatus(?string $moderationStatus): self
    {
        $this->moderationStatus = $moderationStatus;

        return $this;
    }

}
