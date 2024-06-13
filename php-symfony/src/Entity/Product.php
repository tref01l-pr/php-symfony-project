<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 1500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'product')]
    private Collection $madeOrders;

    public function __construct()
    {
        $this->madeOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getMadeOrders(): Collection
    {
        return $this->madeOrders;
    }

    public function addMadeOrder(Order $madeOrder): static
    {
        if (!$this->madeOrders->contains($madeOrder)) {
            $this->madeOrders->add($madeOrder);
            $madeOrder->setProduct($this);
        }

        return $this;
    }

    public function removeMadeOrder(Order $madeOrder): static
    {
        if ($this->madeOrders->removeElement($madeOrder)) {
            // set the owning side to null (unless already changed)
            if ($madeOrder->getProduct() === $this) {
                $madeOrder->setProduct(null);
            }
        }

        return $this;
    }
}
