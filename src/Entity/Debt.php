<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DebtRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DebtRepository::class)]
class Debt
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "idDebt", type: "integer", nullable: false)]
    private int $iddebt;

    #[ORM\Column(name: "amount", type: "float", precision: 10, scale: 0, nullable: false)]
    //#[Assert\NotBlank(message:"Amount cannot be empty")]
    #[Assert\Type(type: "float", message: "Amount must be a valid float value")]
    #[Assert\GreaterThan(value: 0, message: "Amount must be greater than 0")]
    private float $amount;

    #[ORM\Column(name: "paymentDate", type: "date", nullable: false)]
    #[Assert\NotBlank(message: "payment date cannot be empty")]
    #[Assert\GreaterThan(propertyPath: "creationdate", message: "Due date must be after creation date")]
    private $paymentdate;

    #[ORM\Column(name: "amountToPay", type: "float", precision: 10, scale: 0, nullable: false)]
    #[Assert\NotBlank(message: "Amount to pay cannot be empty")]
    #[Assert\Type(type: "float", message: "Amount to pay must be a valid float value")]
    #[Assert\GreaterThan(value: 0, message: "Amount must be greater than 0")]
    private float $amounttopay;

    #[ORM\Column(name: "InterestRate", type: "float", precision: 10, scale: 0, nullable: false)]
    #[Assert\NotBlank(message: "Interest rate cannot be empty")]
    #[Assert\Type(type: "float", message: "Interest rate must be a valid float value")]
    #[Assert\GreaterThan(value: 0, message: "Amount must be greater than 0")]
    private float $interestrate;

    #[ORM\Column(name: "creationDate", type: "date", nullable: false)]
    #[Assert\NotBlank(message: "Creation date cannot be empty")]
    #[Assert\Type(type: "\DateTimeInterface", message: "Creation date must be a valid date")]
    private $creationdate;

    #[ORM\ManyToOne(targetEntity: "Debtcategory")]
    #[ORM\JoinColumn(name: "type", referencedColumnName: "NameDebt")]
    #[Assert\NotBlank(message: "Type cannot be empty")]
    private $type;

    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(name: "idWallet", referencedColumnName: "idwallet")]
    private ?Wallet $idWallet;

    public function getIddebt(): ?int
    {
        return $this->iddebt;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaymentdate(): ?\DateTimeInterface
    {
        return $this->paymentdate;
    }

    public function setPaymentdate(\DateTimeInterface $paymentdate): static
    {
        $this->paymentdate = $paymentdate;

        return $this;
    }

    public function getAmounttopay(): ?float
    {
        return $this->amounttopay;
    }

    public function setAmounttopay(float $amounttopay): static
    {
        $this->amounttopay = $amounttopay;

        return $this;
    }

    public function getInterestrate(): ?float
    {
        return $this->interestrate;
    }

    public function setInterestrate(float $interestrate): static
    {
        $this->interestrate = $interestrate;

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

    public function getType(): ?Debtcategory
    {
        return $this->type;
    }

    public function setType(?Debtcategory $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getIdwallet(): ?Wallet
    {
        return $this->idWallet;
    }

    public function setIdwallet(?Wallet $idwallet): static
    {
        $this->idWallet = $idwallet;

        return $this;
    }
}