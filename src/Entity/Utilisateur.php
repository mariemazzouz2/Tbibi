<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Specialite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ID
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Email
    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    #[Assert\Regex(pattern: "/@.*\./", message: "L'email doit contenir '@' et un point '.'")]
    private ?string $email = null;

    // Roles
    #[ORM\Column]
    private array $roles = [];

    // Password
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 8 caractères.")]
    #[Assert\Regex(pattern: "/[!@#$%^&*(),.?\":{}|<>]/", message: "Le mot de passe doit contenir au moins un caractère spécial.")]
    private ?string $password = null;

    // Name
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Regex(pattern: "/^[A-Z][a-z]+$/", message: "Le nom doit commencer par une majuscule et contenir uniquement des lettres.")]
    private ?string $nom = null;

    // First name
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Regex(pattern: "/^[A-Z][a-z]+$/", message: "Le prénom doit commencer par une majuscule et contenir uniquement des lettres.")]
    private ?string $prenom = null;

    // Phone
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire.")]
    #[Assert\Regex(pattern: "/^\d{8}$/", message: "Le numéro de téléphone doit contenir exactement 8 chiffres.")]
    private ?int $telephone = null;

    // Address
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(min: 4, minMessage: "L'adresse doit contenir au moins 4 caractères.")]
    private ?string $adresse = null;

    // Birthdate
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateNaissance = null;

    // Physical attributes
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $taille = null;

    #[ORM\Column(nullable: true)]
    private ?int $poids = null;

    // Gender
    #[ORM\Column(length: 255)]
    private ?string $sexe = null;

    // Image
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // Status
    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    // Diploma
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diplome = null;

    // Speciality (enum)
    #[ORM\Column(type: 'string', enumType: Specialite::class, nullable: true)]
    private ?Specialite $specialite = null;

    // Relationships with other entities
    #[ORM\ManyToMany(targetEntity: Evenement::class, mappedBy: 'participants')]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Consultation::class)]
    private Collection $consultationsMedecin;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Consultation::class)]
    private Collection $consultationsPatient;

    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'utilisateur', orphanRemoval: true)]
    private Collection $commandes;

    #[ORM\OneToOne(mappedBy: "utilisateur", targetEntity: DossierMedical::class, cascade: ["persist", "remove"])]
    private ?DossierMedical $dossierMedical = null;

    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'patient')]
    private Collection $questions;

    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'medecin')]
    private Collection $reponses;

    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'medecin')]
    private Collection $votes;

    // Face encoding
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $faceEncoding = null;

    // Notifications
    #[ORM\OneToMany(targetEntity: Notii::class, mappedBy: 'touser')]
    private Collection $notiis;
 /**
     * @var Collection<int, Evenement>
     */
    #[ORM\ManyToMany(targetEntity: Evenement::class, mappedBy: 'participation')]
    private Collection $no;
    // Constructor
    public function __construct()
    {
        $this->evenements = new ArrayCollection();
        $this->consultationsMedecin = new ArrayCollection();
        $this->consultationsPatient = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->reponses = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->notiis = new ArrayCollection();
    }

    // Getter and setter methods for each property

    // ID
    public function getId(): ?int { return $this->id; }

    // Email
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    // UserIdentifier
    public function getUserIdentifier(): string { return (string)$this->email; }

    // Roles
    public function getRoles(): array { $roles = $this->roles; $roles[] = 'ROLE_USER'; return array_unique($roles); }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    // Password
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    // Erase credentials
    public function eraseCredentials(): void {}

    // Name
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    // First Name
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    // Phone
    public function getTelephone(): ?int { return $this->telephone; }
    public function setTelephone(int $telephone): static { $this->telephone = $telephone; return $this; }

    // Status
    public function getStatus(): ?int { return $this->status; }
    public function setStatus(int $status): static { $this->status = $status; return $this; }

    // Address
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(string $adresse): static { $this->adresse = $adresse; return $this; }

    // Date of Birth
    public function getDateNaissance(): ?\DateTimeInterface { return $this->dateNaissance; }
    public function setDateNaissance(\DateTimeInterface $dateNaissance): static { $this->dateNaissance = $dateNaissance; return $this; }

    // Physical attributes
    public function getTaille(): ?float { return $this->taille; }
    public function setTaille(?float $taille): static { $this->taille = $taille; return $this; }

    public function getPoids(): ?int { return $this->poids; }
    public function setPoids(?int $poids): static { $this->poids = $poids; return $this; }

    // Gender
    public function getSexe(): ?string { return $this->sexe; }
    public function setSexe(string $sexe): static { $this->sexe = $sexe; return $this; }

    // Image
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    // Speciality
    public function getSpecialite(): ?Specialite { return $this->specialite; }
    public function setSpecialite(Specialite $specialite): self { $this->specialite = $specialite; return $this; }

    // Face encoding
    public function getFaceEncoding(): ?string { return $this->faceEncoding; }
    public function setFaceEncoding(?string $faceEncoding): static { $this->faceEncoding = $faceEncoding; return $this; }

    // Notifications
    public function getNotiis(): Collection { return $this->notiis; }
    public function addNotii(Notii $notii): static
    {
        if (!$this->notiis->contains($notii)) {
            $this->notiis->add($notii);
            $notii->setTouser($this);
        }
        return $this;
    }
    public function removeNotii(Notii $notii): static
    {
        if ($this->notiis->removeElement($notii)) {
            if ($notii->getTouser() === $this) {
                $notii->setTouser(null);
            }
        }
        return $this;
    }
      /**
     * @return Collection<int, Evenement>
     */
    public function getNo(): Collection
    {
        return $this->no;
    }

    public function addNo(Evenement $no): static
    {
        if (!$this->no->contains($no)) {
            $this->no->add($no);
            $no->addParticipation($this);
        }

        return $this;
    }

    public function removeNo(Evenement $no): static
    {
        if ($this->no->removeElement($no)) {
            $no->removeParticipation($this);
        }

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
    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(?string $diplome): static
    {
        $this->diplome = $diplome;

        return $this;
    }
}
