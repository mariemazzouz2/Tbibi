<?php

namespace App\Entity;

use App\Repository\MesureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MesureRepository::class)]
class Mesure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datemesure = null;

    #[ORM\Column]
    private ?float $mesure = null;

    #[ORM\Column(length: 255)]
    private ?string $unité = null;

    #[ORM\ManyToOne(inversedBy: 'mesures')]
    private ?Dossiermedical $doss = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDatemesure(): ?\DateTimeInterface
    {
        return $this->datemesure;
    }

    public function setDatemesure(\DateTimeInterface $datemesure): static
    {
        $this->datemesure = $datemesure;

        return $this;
    }

    public function getMesure(): ?float
    {
        return $this->mesure;
    }

    public function setMesure(float $mesure): static
    {
        $this->mesure = $mesure;

        return $this;
    }

    public function getUnité(): ?string
    {
        return $this->unité;
    }

    public function setUnité(string $unité): static
    {
        $this->unité = $unité;

        return $this;
    }

    public function getDoss(): ?Dossiermedical
    {
        return $this->doss;
    }

    public function setDoss(?Dossiermedical $doss): static
    {
        $this->doss = $doss;

        return $this;
    }
}
