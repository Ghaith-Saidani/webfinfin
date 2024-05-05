<?php

namespace App\Entity;
use App\Entity\Todolist;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idtask = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Description is required!!")]
    #[Assert\Length(
        max: 50,
        min: 4,
        maxMessage: "Description cannot be longer than {{ limit }} characters.",
        minMessage: "Description cannot be less than {{ limit }} characters."
    )]
    private ?string $descriptiontask = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Amount to pay is required.")]
    #[Assert\Positive(message: "Amount to pay must be a positive number.")]
    private ?float $mtapayer = null;

        
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Priority is required.")]
    #[Assert\Choice(choices: ['low', 'medium', 'high'], message: "Invalid priority.")]
    private ?string $priority = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: "creationdate", message: "Due date must be after creation date")]
    private \DateTime $duedate;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Status is required.")]
    #[Assert\Choice(choices: ['done', 'not done'], message: "Invalid status.")]
    private ?string $statustask = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $creationdate;

    public function __construct()
    {
        $this->creationdate = new \DateTime();
        $this->duedate = new \DateTime();
    }

    #[ORM\ManyToOne(targetEntity: Todolist::class)]
    #[ORM\JoinColumn(nullable: true, name: "idtodo", referencedColumnName: "idtodo")]
    #[Assert\NotBlank(message: "Please choose a valid list.")]
    private ?Todolist $idtodo = null;


    #[ORM\ManyToOne(targetEntity: Subcategory::class)]
    #[ORM\JoinColumn(nullable:false, name: "idsubcategory", referencedColumnName: "idsubcategory")]
    #[Assert\NotBlank(message: "SubCategory is required.")]
    private ?Subcategory $idsubcategory = null;
    // #[ORM\Column]
    // private ?int $idsubcategory = null;

    public function getIdtask(): ?int
    {
        return $this->idtask;
    }

    public function getDescriptiontask(): ?string
    {
        return $this->descriptiontask;
    }

public function setDescriptiontask(?string $descriptiontask): static
{
    $this->descriptiontask = $descriptiontask ?? '';

    return $this;
}


public function getMtapayer(): ?float
{
    return $this->mtapayer;
}

public function setMtapayer(?float $mtapayer): static
{
    $this->mtapayer = $mtapayer;

    return $this;
}
    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getDuedate(): ?\DateTimeInterface
    {
        return $this->duedate;
    }

    public function setDuedate(\DateTimeInterface $duedate): static
    {
        $this->duedate = $duedate;

        return $this;
    }

    public function getStatustask(): ?string
    {
        return $this->statustask;
    }
    
    public function setStatustask(?string $statustask): static
    {
        $this->statustask = $statustask;
    
        return $this;
    }

    public function getCreationdate(): ?\DateTimeInterface
    {
        return $this->creationdate;
    }

    public function setCreationdate(?\DateTimeInterface $creationdate): static
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    public function getIdtodo(): ?Todolist
    {
        return $this->idtodo;
    }

    public function setIdtodo(?Todolist $idtodo): void
    {
        $this->idtodo = $idtodo;
    }


    public function getIdsubcategory(): ?Subcategory
    {
        return $this->idsubcategory;
    }
    
    public function setIdsubcategory(?Subcategory $idsubcategory): self
    {
        $this->idsubcategory = $idsubcategory;
        return $this;
    }
    /**
    * @return string
    */
    public function __toString()
    {
        return(string)$this->getIdtodo();
    }

}