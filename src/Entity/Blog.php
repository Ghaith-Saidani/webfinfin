<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type:'text')]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datePublished = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbrOfLikes = null;

    #[ORM\Column(length: 255)]
    private ?string $blogImage = null;

    #[ORM\Column(length: 255)]
    private ?string $tags = null;
    public function __construct()
    {
        $this->datePublished = new \DateTime(); // Set default value to today's date
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getDatePublished(): ?\DateTimeInterface
    {
        return $this->datePublished;
    }

    public function setDatePublished(\DateTimeInterface $datePublished): static
    {
        
        $this->datePublished = $datePublished;

        return $this;
    }

    public function getNbrOfLikes(): ?int
    {
        return $this->nbrOfLikes;
    }

    public function setNbrOfLikes(?int $nbrOfLikes): static
    {
        $this->nbrOfLikes = $nbrOfLikes;

        return $this;
    }

    public function getBlogImage(): ?string
    {
        return $this->blogImage;
    }

    public function setBlogImage(string $blogImage): static
    {
        $this->blogImage = $blogImage;

        return $this;
    }

    
    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(string $tags): static
    {
        $this->tags = $tags;

        return $this;
    }
}
