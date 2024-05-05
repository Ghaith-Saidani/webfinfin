<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ItemCategoryRepository;
use Symfony\Component\Mime\Message;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemCategoryRepository::class)]

class Itemcategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $iditemcategory = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message:"Category name is required")]
    private ?string $nameitemcategory = null;

    public function getIditemcategory(): ?int
    {
        return $this->iditemcategory;
    }

    public function getNameitemcategory(): ?string
    {
        return $this->nameitemcategory;
    }

    public function setNameitemcategory(string $nameitemcategory): static
    {
        $this->nameitemcategory = $nameitemcategory;

        return $this;
    }


}
