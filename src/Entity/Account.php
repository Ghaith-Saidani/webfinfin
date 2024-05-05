<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;
#use App\Repository\AccountRepository;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $idaccount;

    #[ORM\Column(type: "string", length: 15)]
    private $nameaccount;

    #[ORM\Column(type: "string", length: 15)]
    private $typeaccount;

    #[ORM\Column(type: "float")]
    private $balance;

    #[ORM\Column(type: "string", length: 30)]
    private $description;

   // #[ORM\ManyToOne(targetEntity: Wallet::class)]
   // #[ORM\JoinColumn(name: "idWallet", referencedColumnName: "idWallet")]
   // #[ORM\Column(type: "integer")]
   // private $idwallet;

   #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(name: "idWallet", referencedColumnName: "idwallet")]
    private $idwallet;


    public function getIdaccount(): ?int
    {
        return $this->idaccount;
    }

    public function getNameaccount(): ?string
    {
        return $this->nameaccount;
    }

    public function setNameaccount(string $nameaccount): static
    {
        $this->nameaccount = $nameaccount;

        return $this;
    }

    public function getTypeaccount(): ?string
    {
        return $this->typeaccount;
    }

    public function setTypeaccount(string $typeaccount): static
    {
        $this->typeaccount = $typeaccount;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

   public function getIdwallet(): ?Wallet
    {
         return $this->idwallet;
     }

     public function setIdwallet(?Wallet $wallet): static
     {
         $this->idwallet = $wallet;

        return $this;
     }
     public function getCurrencySymbol(): string
     {
         return $this->getIdWallet()->getCurrencySymbol();
     }
     public function getIcon(): string
{
    switch ($this->typeaccount) {
        case 'CHECKING':
            return 'fi fi-rr-bank';
        case 'SAVINGS':
            return 'fi fi-br-piggy-bank';
        case 'CREDIT_CARD':
            return 'fi fi-rr-credit-card';
        case 'CASH':
            return 'fi fi-rr-money-bill-wave-alt';
        default:
            return 'fi fi-rr-question';
    }
}


}
