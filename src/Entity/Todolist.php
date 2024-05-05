<?php

namespace App\Entity;

use App\Repository\TodolistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;
use App\Entity\Task;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TodolistRepository::class)]
#[Broadcast]
class Todolist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idtodo = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Title is required.")]
    #[Assert\Length(
        max: 50,
        min: 4,
        maxMessage: "Title cannot be longer than {{ limit }} characters.",
        minMessage: "Title cannot be less than {{ limit }} characters."
    )]
    private ?string $titletodo = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Status is required.")]
    #[Assert\Choice(choices: ['done', 'not done'], message: "Invalid status.")]
    private ?string $statustodo = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'float', message: "Progress must be a valid number.")]
    private ?float $progress = null;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: "idtodo")]
    private ?Collection $tasks = null;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getIdtodo(): ?int
    {
        return $this->idtodo;
    }

    public function setIdtodo(int $idtodo): self
    {
        $this->idtodo = $idtodo;

        return $this;
    }

    public function getTitletodo(): ?string
    {
        return $this->titletodo;
    }

    public function setTitletodo(?string $titletodo): self
    {
        $this->titletodo = $titletodo;

        return $this;
    }

    public function getStatustodo(): ?string
    {
        return $this->statustodo;
    }

    public function setStatustodo(?string $statustodo): self
    {
        $this->statustodo = $statustodo;

        return $this;
    }

    public function getProgress(): ?float
    {
        return $this->progress;
    }

    public function setProgress(?float $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setIdtodo($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getIdtodo() === $this) {
                $task->setIdtodo(null);
            }
        }

        return $this;
    }


    public function calculateProgress(): ?float
{
    $tasks = $this->getTasks();
    
    if ($tasks === null || $tasks->isEmpty()) {
        return 0.0;
    }

    $totalTasks = count($tasks);
    
    $completedTasks = 0;
    foreach ($tasks as $task) {
        if ($task->getStatustask() === 'done') {
            $completedTasks++;
        }
    }

    $progressPercentage = ($completedTasks / $totalTasks) * 100;
    return round($progressPercentage, 2);
}

    
    public function __toString(): string
    {
        return $this->titletodo ?? '';
    }

    public function calculateAverageTimeBetweenCreationAndDueDate(): ?float
    {
        $tasks = $this->getTasks();
        $taskCount = $tasks->count();
        
        if ($taskCount === 0) {
            return null;
        }
        
        $totalTime = 0;

        foreach ($tasks as $task) {
            $creationDate = $task->getCreationDate();
            $dueDate = $task->getDueDate();
            
            $difference = $dueDate->diff($creationDate);
            $totalTime += $difference->days;
        }

        $averageTime = $totalTime / $taskCount;

        return $averageTime;
    }

    public function getCompletedTaskCount(): int
    {
        $tasks = $this->getTasks();
        $completedTaskCount = 0;

        foreach ($tasks as $task) {
            if ($task->getStatustask() === 'done') {
                $completedTaskCount++;
            }
        }

        return $completedTaskCount;
    }
    public function countCompletedTasks(): int
    {
        $tasks = $this->getTasks();
        $completedTaskCount = 0;

        foreach ($tasks as $task) {
            if ($task->getStatustask() === 'done') {
                $completedTaskCount++;
            }
        }

        return $completedTaskCount;
    }
}