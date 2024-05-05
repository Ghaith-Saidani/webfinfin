<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\ORM\Mapping as ORM;
#use App\Repository\WalletRepository;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $idwallet;

    #[ORM\Column(type: "string", length: 50)]
    private $name;
    #[ORM\Column(type: "string", length: 3)]
    private $currency;

    #[ORM\Column(type: "float")]
    private $totalbalance;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "idUser", referencedColumnName: "iduser")]
    private ?User $iduser;

    public function getIdwallet(): ?int
    {
        return $this->idwallet;
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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTotalbalance(): ?float
    {
        return $this->totalbalance;
    }

    public function setTotalbalance(float $totalbalance): static
    {
        $this->totalbalance = $totalbalance;

        return $this;
    }

    public function getIduser(): ?User
    {
        return $this->iduser;
    }

    public function setIduser(?User $iduser): static
    {
        $this->iduser = $iduser;

        return $this;
    }

    public function getCurrencySymbol(): string
    {
        switch ($this->currency) {
            case 'USD':
                return '$';
            case 'EUR':
                return '€';
            case 'TND':
                return 'TND';
            default:
                return ''; // Default to empty string if currency symbol not found
        }
    }






}
