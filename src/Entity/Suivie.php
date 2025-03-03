<?php

namespace App\Entity;

use App\Repository\SuivieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuivieRepository::class)]
class Suivie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeS = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateSuivie = null;

    #[ORM\Column(length: 255)]
    private ?string $donnees = null;

    #[ORM\ManyToOne(targetEntity: DossierMedical::class, inversedBy: "suivis")]
    #[ORM\JoinColumn(nullable: false)]
    private ?DossierMedical $dossierMedical = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeS(): ?string
    {
        return $this->typeS;
    }

    public function setTypeS(string $typeS): static
    {
        $this->typeS = $typeS;

        return $this;
    }

    public function getDateSuivie(): ?\DateTimeInterface
    {
        return $this->dateSuivie;
    }

    public function setDateSuivie(\DateTimeInterface $dateSuivie): static
    {
        $this->dateSuivie = $dateSuivie;

        return $this;
    }

    public function getDonnees(): ?string
    {
        return $this->donnees;
    }

    public function setDonnees(string $donnees): static
    {
        $this->donnees = $donnees;

        return $this;
    }
    public function getDossierMedical(): ?DossierMedical
    {
        return $this->dossierMedical;
    }

    public function setDossierMedical(?DossierMedical $dossierMedical): static
    {
        $this->dossierMedical = $dossierMedical;
        return $this;
    }
}
