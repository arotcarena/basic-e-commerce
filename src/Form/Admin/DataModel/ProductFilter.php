<?php
namespace App\Form\Admin\DataModel;

use App\Entity\Category;
use App\Entity\SubCategory;

class ProductFilter
{
    private ?Category $category = null;

    private ?SubCategory $subCategory = null;

    private ?int $minPrice = null;

    private ?int $maxPrice = null;

    private ?string $q = null;

    private ?string $sortBy = null;

    private ?int $minStock = null;

    private ?int $maxStock = null;


    /**
     * Get the value of maxStock
     */ 
    public function getMaxStock()
    {
        return $this->maxStock;
    }

    /**
     * Set the value of maxStock
     *
     * @return  self
     */ 
    public function setMaxStock($maxStock)
    {
        $this->maxStock = $maxStock;

        return $this;
    }

    /**
     * Get the value of minStock
     */ 
    public function getMinStock()
    {
        return $this->minStock;
    }

    /**
     * Set the value of minStock
     *
     * @return  self
     */ 
    public function setMinStock($minStock)
    {
        $this->minStock = $minStock;

        return $this;
    }


    /**
     * Get the value of q
     */ 
    public function getQ()
    {
        return $this->q;
    }

    /**
     * Set the value of q
     *
     * @return  self
     */ 
    public function setQ($q)
    {
        $this->q = $q;

        return $this;
    }

    /**
     * Get the value of maxPrice
     */ 
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * Set the value of maxPrice
     *
     * @return  self
     */ 
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    /**
     * Get the value of minPrice
     */ 
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * Set the value of minPrice
     *
     * @return  self
     */ 
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    /**
     * Get the value of sortBy
     */ 
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set the value of sortBy
     *
     * @return  self
     */ 
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * Get the value of subCategory
     */ 
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the value of subCategory
     *
     * @return  self
     */ 
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the value of category
     */ 
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @return  self
     */ 
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }
}