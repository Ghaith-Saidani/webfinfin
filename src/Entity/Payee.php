<?php

namespace App\Entity;

use App\Repository\PayeeRepository;
use Doctrine\ORM\Mapping as ORM;
#use App\Repository\PayeeRepository;

#[ORM\Entity(repositoryClass: PayeeRepository::class)]
class Payee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $idpayee;

    #[ORM\Column(type: "string", length: 20)]
    private $namepayee;
    
    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(name: "idWallet", referencedColumnName: "idwallet")]
    private ?Wallet $idwallet;


  
 

    public function getIdpayee(): ?int
    {
        return $this->idpayee;
    }

    public function getNamepayee(): ?string
    {
        return $this->namepayee;
    }

    public function setNamepayee(string $namepayee): static
    {
        $this->namepayee = $namepayee;

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

}
