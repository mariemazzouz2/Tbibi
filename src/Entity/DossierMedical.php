<?php
namespace App\Entity;

use App\Repository\DossierMedicalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DossierMedicalRepository::class)]
class DossierMedical
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "La date doit être un objet valide de type DateTime.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToOne(inversedBy: "dossierMedical", targetEntity: Utilisateur::class, cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 255)]
    /**
     * @Assert\NotBlank(message="Le fichier est obligatoire.")
     * @Assert\File(mimeTypes={"application/pdf", "image/jpeg", "image/png"}, message="Le fichier doit être un PDF, JPEG ou PNG.")
     */
    private ?string $fichier = null;


#[ORM\Column(length: 255)]
#[Assert\NotBlank(message: "L'unité est obligatoire.")]
#[Assert\Length(min: 2, max: 50, minMessage: "L'unité doit contenir au moins {{ limit }} caractères.", maxMessage: "L'unité ne doit pas dépasser {{ limit }} caractères.")]
private ?string $unite = null;


    #[ORM\Column]
    #[Assert\NotBlank(message: "La mesure est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "La mesure doit être un nombre valide.")]
    #[Assert\GreaterThan(value: 0, message: "La mesure doit être supérieure à 0.")]
    private ?float $mesure = null;

    /**
     * @var Collection<int, Analyse>
     */
    #[ORM\OneToMany(targetEntity: Analyse::class, mappedBy: 'dossier')]
    private Collection $analyses;

    public function __construct()
    {
        $this->analyses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): static
    {
        $this->unite = $unite;

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

    /**
     * @return Collection<int, Analyse>
     */
    public function getAnalyses(): Collection
    {
        return $this->analyses;
    }

    public function addAnalysis(Analyse $analysis): static
    {
        if (!$this->analyses->contains($analysis)) {
            $this->analyses->add($analysis);
            $analysis->setDossier($this);
        }

        return $this;
    }

    public function removeAnalysis(Analyse $analysis): static
    {
        if ($this->analyses->removeElement($analysis)) {
            // set the owning side to null (unless already changed)
            if ($analysis->getDossier() === $this) {
                $analysis->setDossier(null);
            }
        }

        return $this;
    }
}
