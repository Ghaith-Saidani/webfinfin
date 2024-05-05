<?php

namespace App\Entity;
use App\Entity\Wishlist;
use App\Entity\Itemcategory;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\WishListItemRepository;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: WishListItemRepository::class)]

class Wishlistitem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idwishlistitem = null;

    #[ORM\Column(length: 255)]    
    #[Assert\NotBlank(message:"Wishlist Item name is required")]

    private ?string $namewishlistitem = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"Price is required")]
    #[Assert\Range(min: 0.01, minMessage: "Price must be greater than 0")]

    private ?float $price = null;

    #[ORM\Column(name: "creationDate", type: "date", nullable: false)]
    private $creationdate;

   
    #[ORM\Column(length: 10)]    
    #[Assert\NotBlank(message:"priority is required")]

    private ?string $priority = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"progress is required")]


    private ?float $progress = null;


    #[ORM\Column(length: 255, nullable: true)]   
    #[Assert\NotBlank(message:"Status is required")]


     private ?string $status;

    
    #[ORM\Column]    
    private ?bool $emailSent = false;

    
    #[ORM\ManyToOne(targetEntity: Itemcategory::class)]
    #[ORM\JoinColumn(name: "idItemCategory", referencedColumnName: "iditemcategory")]
    #[Assert\NotBlank(message:"Category Item is required")]

    private $iditemcategory;
    
    
    
    #[ORM\ManyToOne(targetEntity: Wishlist::class)]
    #[ORM\JoinColumn(name: "idWishlist", referencedColumnName: "idwishlist")]
    #[Assert\NotBlank(message:"Wishlist name is required")]

    private $idwishlist;

    public function getIdwishlistitem(): ?int
    {
        return $this->idwishlistitem;
    }

    public function getNamewishlistitem(): ?string
    {
        return $this->namewishlistitem;
    }

    public function setNamewishlistitem(string $namewishlistitem): static
    {
        $this->namewishlistitem = $namewishlistitem;

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

    public function getCreationdate(): ?\DateTimeInterface
    {
        return $this->creationdate;
    }

    public function setCreationdate(\DateTimeInterface $creationdate): static
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getProgress(): ?float
    {
        return $this->progress;
    }

    public function setProgress(?float $progress): static
    {
        $this->progress = $progress;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isEmailSent(): ?bool
    {
        return $this->emailSent;
    }

    public function setEmailSent(bool $emailSent): static
    {
        $this->emailSent = $emailSent;

        return $this;
    }

    public function getIditemcategory(): ?Itemcategory
    {
        return $this->iditemcategory;
    }

    public function setIditemcategory(?Itemcategory $iditemcategory): static
    {
        $this->iditemcategory = $iditemcategory;

        return $this;
    }

    public function getIdwishlist(): ?Wishlist
    {
        return $this->idwishlist;
    }

    public function setIdwishlist(?Wishlist $idwishlist): static
    {
        $this->idwishlist = $idwishlist;

        return $this;
    }


}
