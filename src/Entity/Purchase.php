<?php

namespace App\Entity;

use App\Config\SiteConfig;
use App\Repository\PurchaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('ref', message: 'purchase ref must be unique')]
#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[Assert\Length(max: 200)]
    #[ORM\Column(nullable: true, length: 255)]
    private ?string $ref = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[Assert\Positive()]
    #[ORM\Column]
    private ?int $totalPrice = 0;

    #[Assert\Choice(choices: [SiteConfig::STATUS_PENDING, SiteConfig::STATUS_PAID, SiteConfig::STATUS_SENT, SiteConfig::STATUS_DELIVERED, SiteConfig::STATUS_CANCELED])]
    #[ORM\Column(length: 255)]
    private ?string $status = SiteConfig::STATUS_PENDING;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(mappedBy: 'purchase', targetEntity: PurchaseLine::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $purchaseLines;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?PostalDetail $deliveryDetail = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?PostalDetail $invoiceDetail = null;


    public function __construct()
    {
        $this->purchaseLines = new ArrayCollection();
    }

    /**
     * Pour l'affichage de la purchase : si strong, on ajoute la classe strong
     *
     * @return boolean
     */
    public function isStrong(): bool
    {
        if($this->status === SiteConfig::STATUS_DELIVERED || $this->status === SiteConfig::STATUS_CANCELED)
        {
            return false;
        }
        return true;
    }

    public function getStatusLabel(): string
    {
        return SiteConfig::STATUS_LABELS[$this->status];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
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

    public function getTotalPrice(): ?int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;

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

    /**
     * @return Collection<int, PurchaseLine>
     */
    public function getPurchaseLines(): Collection
    {
        return $this->purchaseLines;
    }

    public function addPurchaseLine(PurchaseLine $purchaseLine): self
    {
        if (!$this->purchaseLines->contains($purchaseLine)) {
            $this->purchaseLines->add($purchaseLine);
            $purchaseLine->setPurchase($this);
        }

        return $this;
    }

    public function removePurchaseLine(PurchaseLine $purchaseLine): self
    {
        if ($this->purchaseLines->removeElement($purchaseLine)) {
            // set the owning side to null (unless already changed)
            if ($purchaseLine->getPurchase() === $this) {
                $purchaseLine->setPurchase(null);
            }
        }

        return $this;
    }

    public function getDeliveryDetail(): ?PostalDetail
    {
        return $this->deliveryDetail;
    }

    public function setDeliveryDetail(?PostalDetail $deliveryDetail): self
    {
        $this->deliveryDetail = $deliveryDetail;

        return $this;
    }

    public function getInvoiceDetail(): ?PostalDetail
    {
        return $this->invoiceDetail;
    }

    public function setInvoiceDetail(?PostalDetail $invoiceDetail): self
    {
        $this->invoiceDetail = $invoiceDetail;

        return $this;
    }

}
