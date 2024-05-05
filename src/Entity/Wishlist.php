<?php

namespace App\Entity;
use App\Entity\Wallet;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\WishListRepository;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;





#[ORM\Entity(repositoryClass: WishListRepository::class)]

class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idwishlist = null;


    
    #[ORM\Column(length: 100)]    
    #[Assert\NotBlank(message:"Wishlist name is required")]


    private ?string $namewishlist = null;

    #[ORM\Column]    
    #[Assert\NotBlank(message:"Monthly Budget is required")]
    #[Assert\Range(min: 0.01, minMessage: "Price must be greater than 0")]


    private ?float $monthlybudget = null;

    
    #[ORM\Column(name: "creationDate", type: "date", nullable: false)]
    private $creationdate;

    #[ORM\Column]    

    private ?float $savedbudget = 0;


    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(name: "idwallet", referencedColumnName: "idwallet")]
    private ?Wallet $idwallet;

    public function getIdwishlist(): ?int
    {
        return $this->idwishlist;
    }

    public function getNamewishlist(): ?string
    {
        return $this->namewishlist;
    }

    public function setNamewishlist(string $namewishlist): static
    {
        $this->namewishlist = $namewishlist;

        return $this;
    }

    public function getMonthlybudget(): ?float
    {
        return $this->monthlybudget;
    }

    public function setMonthlybudget(float $monthlybudget): static
    {
        $this->monthlybudget = $monthlybudget;

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

    public function getSavedbudget(): ?float
    {
        return $this->savedbudget;
    }

    public function setSavedbudget(float $savedbudget): static
    {
        $this->savedbudget = $savedbudget;

        return $this;
    }

    public function getIdwallet(): ?Wallet
    {
        return $this->idwallet;
    }

    public function setIdwallet(?Wallet $idwallet): static
    {
        $this->idwallet = $idwallet;

        return $this;
    }


}
