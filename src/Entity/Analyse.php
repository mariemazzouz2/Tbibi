<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\AnalyseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyseRepository::class)]
class Analyse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)] 
    #[Assert\NotBlank(message: "Le type d'analyse ne peut pas être vide.")]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateanalyse = null;

    #[ORM\Column(length: 255)]
    private ?string $donnees_Analyse = null;

    #[ORM\Column(length: 255)]
    private ?string $diagnostic = null;

    #[ORM\ManyToOne(inversedBy: 'analyses')]
    private ?DossierMedical $dossier = null;

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

    public function getDateanalyse(): ?\DateTimeInterface
    {
        return $this->dateanalyse;
    }

    public function setDateanalyse(\DateTimeInterface $dateanalyse): static
    {
        $this->dateanalyse = $dateanalyse;

        return $this;
    }

    public function getDonneesAnalyse(): string
    {
        return $this->donnees_Analyse;
    }

    public function setDonneesAnalyse(string $donnees_Analyse): static
    {
        $this->donnees_Analyse = $donnees_Analyse;

        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(string $diagnostic): static
    {
        $this->diagnostic = $diagnostic;

        return $this;
    }

    public function getDossier(): ?DossierMedical
    {
        return $this->dossier;
    }

    public function setDossier(?DossierMedical $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }
}
