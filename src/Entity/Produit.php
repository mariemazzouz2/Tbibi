<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix ne peut pas être nul.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le chemin de l'image ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $image = null;

    // Add this field for handling file uploads
    #[Assert\Image(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/jpg'],
        mimeTypesMessage: 'Veuillez uploader une image valide (JPEG, PNG)',
        allowLandscape: true,
        allowPortrait: true
    )]
    private $imageFile;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'produit')]
    private ?Commande $commande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;
        return $this;
    }
    
    /**
     * String representation of this class
     */
    public function __toString(): string
    {
        return $this->nom ?? 'Produit #' . $this->id;
    }
}
