<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private ?float $montantTotal = 0; 

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de commande est requise.")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "La date de commande doit être une date valide.")]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    #[Assert\Choice(choices: ["En cours", "Expédiée", "Livrée", "Annulée"], message: "Le statut doit être 'En cours', 'Expédiée', 'Livrée' ou 'Annulée'.")]
    private ?string $statut = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'commande')]
    private Collection $produit;

    #[ORM\ManyToOne(inversedBy: 'com')]
    #[Assert\NotNull(message: "L'utilisateur associé à la commande est obligatoire.")]
    private ?Utilisateur $user = null;

    public function __construct()
    {
        $this->produit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


public function getMontantTotal(): float
{
    return $this->montantTotal ?? 0;
}

public function setMontantTotal(float $montantTotal): self
{
    $this->montantTotal = $montantTotal;
    return $this;
}


    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduit(): Collection
    {
        return $this->produit;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produit->contains($produit)) {
            $this->produit->add($produit);
            $produit->setCommande($this);
        }
        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produit->removeElement($produit)) {
            if ($produit->getCommande() === $this) {
                $produit->setCommande(null);
            }
        }
        return $this;
    }

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): static
    {
        $this->user = $user;
        return $this;
    }
    public function calculerMontantTotal(): void
{
    $total = 0;
    foreach ($this->produit as $produit) {
        $total += $produit->getPrix();
    }
    $this->montantTotal = $total;

}
}

    

