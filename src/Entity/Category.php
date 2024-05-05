<?php

namespace App\Entity;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $idcategory;

    #[ORM\Column(type: "string", length: 15)]
    #[Assert\NotBlank(message:'Le nom ne peut pas etre vide')]
    #[Assert\Regex(
        pattern:"/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/",
        message:"Veuillez saisir uniquement des lettres")]
    private $name;
    #[ORM\Column(type: "float")]
    #[Assert\Regex(
        pattern:"/^\d+(\.\d{1,2})?$/",
        message:"Le champ budgetlimit doit être une valeur monétaire valide."
    )]
    private $budgetlimit;

    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(name: "idWallet", referencedColumnName: "idwallet")]
    private $idwallet;

    #[ORM\OneToMany(targetEntity: Subcategory::class, mappedBy: 'category')]
    private Collection $subCategories;

  

    public function getIdcategory(): ?int
    {
        return $this->idcategory;
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

    public function getBudgetlimit(): ?float
    {
        return $this->budgetlimit;
    }

    public function setBudgetlimit(float $budgetlimit): static
    {
        $this->budgetlimit = $budgetlimit;

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
    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
    }

    /**
     * @return Collection|Subcategory[]
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(Subcategory $subCategory): self
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories[] = $subCategory;
            $subCategory->setIdcategory($this);
        }

        return $this;
    }

    public function removeSubCategory(Subcategory $subCategory): self
    {
        if ($this->subCategories->removeElement($subCategory)) {
            // set the owning side to null (unless already changed)
            if ($subCategory->getIdcategory() === $this) {
                $subCategory->setIdcategory(null);
            }
        }

        return $this;
    }

}