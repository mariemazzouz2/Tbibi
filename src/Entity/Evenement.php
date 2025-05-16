<?php
namespace App\Entity;


use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Statut;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\CategorieEv;
use App\Entity\DemandeParticipation;
use App\Entity\Utilisateur;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "Veuillez entrer une date valide.")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de fin est obligatoire.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "Veuillez entrer une date valide.")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début.")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le lieu ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le lieu doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le lieu ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $lieu = null;

    #[ORM\Column(type: 'string', enumType: Statut::class)]
    #[Assert\NotNull(message: "Le statut est obligatoire.")]
    #[Assert\Choice(choices: [Statut::EN_ATTENTE, Statut::CONFIRME, Statut::ANNULE, Statut::TERMINE, Statut::INACTIF, Statut::ACTIF], message: "Le statut doit être valide.")]
    private Statut $statut;

    #[ORM\ManyToOne(targetEntity: CategorieEv::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La catégorie est obligatoire.")]
    private ?CategorieEv $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le chemin de l'image ne peut pas dépasser 255 caractères."
    )]
    private ?string $image = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'evenements')]
    #[ORM\JoinTable(name: 'participe')]
    private Collection $participation;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: DemandeParticipation::class)]
    private Collection $demandesParticipation;

    public function __construct()
    {
        $this->participation = new ArrayCollection();
        $this->demandesParticipation = new ArrayCollection();
    }
    
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

    public function setStatut(Statut $statut): static
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getParticipation(): Collection
    {
        return $this->participation;
    }

    public function addParticipation(Utilisateur $user): self
    {
        if (!$this->participation->contains($user)) {
            $this->participation->add($user);
        }

        return $this;
    }

    public function removeParticipation(Utilisateur $user): self
    {
        $this->participation->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, DemandeParticipation>
     */
    public function getDemandesParticipation(): Collection
    {
        return $this->demandesParticipation;
    }

    public function addDemandesParticipation(DemandeParticipation $demandesParticipation): self
    {
        if (!$this->demandesParticipation->contains($demandesParticipation)) {
            $this->demandesParticipation->add($demandesParticipation);
            $demandesParticipation->setEvenement($this);
        }

        return $this;
    }

    public function removeDemandesParticipation(DemandeParticipation $demandesParticipation): self
    {
        if ($this->demandesParticipation->removeElement($demandesParticipation)) {
            if ($demandesParticipation->getEvenement() === $this) {
                $demandesParticipation->setEvenement(null);
            }
        }

        return $this;
    }
}
