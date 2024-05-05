<?php

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Assert\Regex;

#[ORM\Entity(repositoryClass: SubCategoryRepository::class)]
class Subcategory
{   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idsubcategory = null;


    #[ORM\Column(type: "string", length: 15)]
    private $name;

    #[ORM\Column(type: "float")]
    private $mtAssigné;

    #[ORM\Column(type: "float")]
    private $mtDépensé;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: "subcategories")]
    #[ORM\JoinColumn(name: "idcategory", referencedColumnName: "idcategory")]
    private $idcategory;

    /*
    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: "idsubcategory")]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: "idtask")]
    private $task;
    */

    // #[ORM\OneToMany(targetEntity: Task::class, mappedBy: "idtodo")]
    // private ?Collection $tasks = null;

    public function getIdsubcategory(): ?int
    {
        return $this->idsubcategory;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMtassigne(): ?float
    {
        return $this->mtAssigné;
    }

    public function setMtassigne(float $mtAssigné): static
    {
        $this->mtAssigné = $mtAssigné;

        return $this;
    }

    public function getmtdepense(): ?float
    {
        return $this->mtDépensé	;
    }

    public function setmtdepense(float $mtDépensé	): static
    {
        $this->mtDépensé	 = $mtDépensé	;

        return $this;
    }

    public function getIdcategory(): ?Category
    {
        return $this->idcategory;
    }

    public function setIdcategory(?Category $idcategory): static
    {
        $this->idcategory = $idcategory;

        return $this;
    }

}