<?php

namespace App\Form\Admin\DataModel;


class ReviewFilter
{
    private ?int $id = null;

    private ?int $rate = null;

    private ?string $moderationStatus = null;

    private ?string $sortBy = null;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getModerationStatus(): ?string
    {
        return $this->moderationStatus;
    }

    public function setModerationStatus(string $moderationStatus): self
    {
        $this->moderationStatus = $moderationStatus;

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(string $sortBy): self
    {
        $this->sortBy = $sortBy;

        return $this;
    }
}
