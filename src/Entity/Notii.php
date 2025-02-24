<?php

namespace App\Entity;

use App\Repository\NotiiRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotiiRepository::class)]
class Notii
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'notiis')]
    private ?Utilisateur $touser = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTouser(): ?Utilisateur
    {
        return $this->touser;
    }

    public function setTouser(?Utilisateur $touser): static
    {
        $this->touser = $touser;

        return $this;
    }
}
