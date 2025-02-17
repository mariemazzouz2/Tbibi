<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Specialite;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-ZÀ-ÿ][A-Za-z\s\-.,éèêëàùûûïîôôà?]+$/",
        message: "Le titre doit commencer par une majuscule et ne contenir que des lettres, des espaces, des tirets, des points, des virgules, des accents et des points d'interrogation."
    )]
    
    
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z].*/",
        message: "Le contenu doit commencer par une majuscule."
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: 'string', enumType: Specialite::class)]
    #[Assert\NotNull(message: "Veuillez sélectionner une spécialité.")]
    private ?Specialite $specialite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\File(
        maxSize: "5M",
        mimeTypes: ["image/jpeg", "image/png"],
        mimeTypesMessage: "Veuillez télécharger une image valide (JPEG/PNG)"
    )]
    private ?string $image = null;

    #[ORM\Column]
    #[Assert\Type(
        type: 'bool',
        message: "La visibilité doit être un booléen (true/false)."
    )]
    private bool $visible = false;
    
    #[ORM\Column]
    private ?DateTimeImmutable $date_creation;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $patient = null;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Reponse::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reponses;

    public function __construct()
    {
        $this->date_creation = new DateTimeImmutable();
        $this->reponses = new ArrayCollection();
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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    public function setSpecialite(?Specialite $specialite): self
    {
        $this->specialite = $specialite;
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

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function getDateCreation(): ?DateTimeImmutable
    {
        return $this->date_creation;
    }

    public function setDateCreation(DateTimeImmutable $dateCreation): static
    {
        $this->date_creation = $dateCreation;
        return $this;
    }

    public function getPatient(): ?Utilisateur
    {
        return $this->patient;
    }

    public function setPatient(?Utilisateur $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setQuestion($this);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getQuestion() === $this) {
                $reponse->setQuestion(null);
            }
        }
        return $this;
    }
}
