<?php

namespace App\Entity;

use App\Enum\TypeVote;
use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $medecin = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reponse $reponse = null;

    #[ORM\Column(enumType: TypeVote::class)]
    private ?TypeVote $valeur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getmedecin(): ?utilisateur
    {
        return $this->medecin;
    }

    public function setmedecin(?utilisateur $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getReponse(): ?reponse
    {
        return $this->reponse;
    }

    public function setReponse(?reponse $reponse): static
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getValeur(): ?TypeVote
    {
        return $this->valeur;
    }

    public function setValeur(TypeVote $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }
}
