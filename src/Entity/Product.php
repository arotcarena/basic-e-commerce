<?php

namespace App\Entity;

use App\Entity\SubCategory;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity('publicRef', message: 'La référence publique doit être unique')]
#[UniqueEntity('privateRef', message: 'La référence privée doit être unique')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'La référence publique est obligatoire')]
    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[ORM\Column(length: 255)]
    private ?string $publicRef = null;

    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $privateRef = null;

    #[Assert\NotBlank(message: 'La désignation est obligatoire')]
    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[ORM\Column(length: 255)]
    private ?string $designation = null;

    #[Assert\Positive(message: 'Le prix doit être supérieur à 0')]
    #[ORM\Column]
    private ?int $price = null;

    #[Assert\PositiveOrZero(message: 'Le stock doit être supérieur ou égal à 0')]
    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Picture::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $pictures;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\ManyToMany(targetEntity: self::class)]
    private Collection $suggestedProducts;

    #[ORM\ManyToOne(inversedBy: 'products', targetEntity: Category::class)]
    private ?Category $category = null;
    
    #[ORM\ManyToOne(inversedBy: 'products', targetEntity: SubCategory::class)]
    private ?SubCategory $subCategory = null;

    #[Assert\Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Slug invalide (format requis : slug-d-une-categorie)')]
    #[Assert\NotBlank(message: 'Le slug est obligatoire')]
    #[Assert\Length(max: 200, maxMessage: '200 caractères maximum')]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(nullable: true)]
    private ?int $countViews = null;

    #[ORM\Column(nullable: true)]
    private ?int $countCarts = null;

    private ?Picture $firstPicture = null;

    #[ORM\Column(nullable: true)]
    private ?int $countSales = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->suggestedProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicRef(): ?string
    {
        return $this->publicRef;
    }

    public function setPublicRef(string $publicRef): self
    {
        $this->publicRef = $publicRef;

        return $this;
    }

    public function getPrivateRef(): ?string
    {
        return $this->privateRef;
    }

    public function setPrivateRef(?string $privateRef): self
    {
        $this->privateRef = $privateRef;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setProduct($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getProduct() === $this) {
                $picture->setProduct(null);
            }
        }

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
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSuggestedProducts(): Collection
    {
        return $this->suggestedProducts;
    }

    public function addSuggestedProduct(self $suggestedProduct): self
    {
        if (!$this->suggestedProducts->contains($suggestedProduct)) {
            $this->suggestedProducts->add($suggestedProduct);
        }

        return $this;
    }

    public function removeSuggestedProduct(self $suggestedProduct): self
    {
        $this->suggestedProducts->removeElement($suggestedProduct);

        return $this;
    }

    /**
     * @return Category|null
     */ 
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category
     *
     * @return  self
     */ 
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return SubCategory|null
     */ 
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * @param SubCategory
     *
     * @return  self
     */ 
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCountViews(): ?int
    {
        return $this->countViews;
    }

    public function setCountViews(?int $countViews): self
    {
        $this->countViews = $countViews;

        return $this;
    }

    public function getCountCarts(): ?int
    {
        return $this->countCarts;
    }

    public function setCountCarts(?int $countCarts): self
    {
        $this->countCarts = $countCarts;

        return $this;
    }

    /**
     * Get the value of firstPicture
     */ 
    public function getFirstPicture()
    {
        return $this->firstPicture;
    }

    /**
     * Set the value of firstPicture
     *
     * @return  self
     */ 
    public function setFirstPicture($firstPicture)
    {
        $this->firstPicture = $firstPicture;

        return $this;
    }

    public function getCountSales(): ?int
    {
        return $this->countSales;
    }

    public function setCountSales(?int $countSales): self
    {
        $this->countSales = $countSales;

        return $this;
    }
}
