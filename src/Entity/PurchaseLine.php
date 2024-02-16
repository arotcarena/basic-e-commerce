<?php

namespace App\Entity;

use App\Repository\PurchaseLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Cascade;

#[ORM\Entity(repositoryClass: PurchaseLineRepository::class)]
class PurchaseLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchaseLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Purchase $purchase = null;

    
    #[ORM\Column]
    private ?int $quantity = null;
    
    #[ORM\Column]
    private ?int $totalPrice = null;
    
    #[ORM\Column(type: Types::JSON)]
    private array $product = [];


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(?Purchase $purchase): self
    {
        $this->purchase = $purchase;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getProduct(): array
    {
        return $this->product;
    }

    public function setProduct(array $product): self
    {
        $this->product = $product;

        return $this;
    }

}
