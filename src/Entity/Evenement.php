<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Statut;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;
    #[ORM\Column(type: 'string', enumType: Statut::class)]
    private Statut $statut;

    #[ORM\ManyToOne(targetEntity: CategorieEv::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategorieEv $categorie = null;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'evenements')]
    #[ORM\JoinTable(name: 'evenement_utilisateur')]
    private Collection $participants;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }
    public function getStatut(): Statut
    {
        return $this->statut;
    }

    public function setStatut(Statut $statut): self
    {
        $this->statut = $statut;
        return $this;
    }
    public function getCategorie(): ?CategorieEv
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieEv $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Utilisateur $utilisateur): static
    {
        if (!$this->participants->contains($utilisateur)) {
            $this->participants->add($utilisateur);
            $utilisateur->addEvenement($this);
        }

        return $this;
    }

    public function removeParticipant(Utilisateur $utilisateur): static
    {
        if ($this->participants->removeElement($utilisateur)) {
            $utilisateur->removeEvenement($this);
        }

        return $this;
    }
}
