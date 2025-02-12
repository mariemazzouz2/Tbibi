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
        assert(!empty($type), 'Le type ne peut pas être vide.');
        $this->type = $type;
        return $this;
    }

    public function getDatemesure(): ?\DateTimeInterface
    {
        return $this->datemesure;
    }

    public function setDatemesure(\DateTimeInterface $datemesure): static
    {
        assert($datemesure <= new \DateTime(), 'La date de la mesure ne peut pas être dans le futur.');
        $this->datemesure = $datemesure;
        return $this;
    }

    public function getMesure(): ?float
    {
        return $this->mesure;
    }

    public function setMesure(float $mesure): static
    {
        assert($mesure >= 0, 'La mesure ne peut pas être négative.');
        $this->mesure = $mesure;
        return $this;
    }

    public function getUnité(): ?string
    {
        return $this->unité;
    }

    public function setUnité(string $unité): static
    {
        assert(!empty($unité), 'L\'unité ne peut pas être vide.');
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
