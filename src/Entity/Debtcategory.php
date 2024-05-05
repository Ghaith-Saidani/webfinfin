<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DebtCategoryRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: "debtcategory")]
#[ORM\Entity(repositoryClass: DebtCategoryRepository::class)]
class Debtcategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "NameDebt", type: "string", length: 255, nullable: false, unique: true)]
    #[Assert\NotBlank(message: "Name debt cannot be empty")]
    private string $NameDebt;

    public function __construct()
    {
        // Set a default value for NameDebt
        $this->NameDebt = 'Default Name';
    }

    public function getNamedebt(): ?string
    {
        return $this->NameDebt;
    }

    public function setNameDebt(?string $NameDebt): self
    {
        $this->NameDebt = $NameDebt;

        return $this;
    }

    public function __toString(): string
    {
        return $this->NameDebt ?? '';
    }
}