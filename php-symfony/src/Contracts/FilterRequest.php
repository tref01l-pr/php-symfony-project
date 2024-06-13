<?php

namespace App\Contracts;

use App\Entity\Category;

class FilterRequest
{
    private ?Category $category = null;
    private ?float $minPrice = null;
    private ?float $maxPrice = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    public function setMinPrice(float $minPrice): static
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(float $maxPrice): static
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }
}