<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Budget
 *
 * @ORM\Table(name="budget", indexes={@ORM\Index(name="fk_constraint_budget", columns={"idWallet"})})
 * @ORM\Entity
 */
class Budget
{
    /**
     * @var int
     *
     * @ORM\Column(name="idBudget", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idbudget;

    /**
     * @var float
     *
     * @ORM\Column(name="totalBudget", type="float", precision=10, scale=0, nullable=false)
     */
    private $totalbudget;

    /**
     * @var float
     *
     * @ORM\Column(name="readyToAssign", type="float", precision=10, scale=0, nullable=false)
     */
    private $readytoassign;

    /**
     * @var \int
     *
     * @ORM\ManyToOne(targetEntity="Wallet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idWallet", referencedColumnName="idWallet")
     * })
     */
    private $idwallet;

    public function getIdbudget(): ?int
    {
        return $this->idbudget;
    }

    public function getTotalbudget(): ?float
    {
        return $this->totalbudget;
    }

    public function setTotalbudget(float $totalbudget): static
    {
        $this->totalbudget = $totalbudget;

        return $this;
    }

    public function getReadytoassign(): ?float
    {
        return $this->readytoassign;
    }

    public function setReadytoassign(float $readytoassign): static
    {
        $this->readytoassign = $readytoassign;

        return $this;
    }

    public function getIdwallet(): ?int
    {
        return $this->idwallet;
    }

    public function setIdwallet(?Wallet $idwallet): static
    {
        $this->idwallet = $idwallet;

        return $this;
    }


}
