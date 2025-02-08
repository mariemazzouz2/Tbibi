<?php

namespace App\Entity;

use App\Enum\TypeProduit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $descriptionP = null;

    #[ORM\Column]
    private ?int $prixP = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(type: 'string', enumType: TypeProduit::class)]
    private TypeProduit $type;

    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'produits')]
private Collection $commandes;

public function __construct()
{
    $this->commandes = new ArrayCollection();
}

public function getCommandes(): Collection
{
    return $this->commandes;
}

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescriptionP(): ?string
    {
        return $this->descriptionP;
    }

    public function setDescriptionP(string $descriptionP): static
    {
        $this->descriptionP = $descriptionP;

        return $this;
    }

    public function getPrixP(): ?int
    {
        return $this->prixP;
    }

    public function setPrixP(int $prixP): static
    {
        $this->prixP = $prixP;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }
    public function getType(): TypeProduit
    {
        return $this->type;
    }

    public function setType(TypeProduit $type): static
    {
        $this->type = $type;
        return $this;
    }
}
