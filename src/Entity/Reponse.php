<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $medecin = null;

    #[ORM\Column(length: 255)]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?DateTimeImmutable $date_reponse;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'reponse', cascade: ['remove'], orphanRemoval: true)]
    private Collection $votes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?question
    {
        return $this->question;
    }

    public function setQuestion(?question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getMedecin(): ?utilisateur
    {
        return $this->medecin;
    }

    public function setMedecin(?utilisateur $medecin): static
    {
        $this->medecin = $medecin;

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
    public function setDateReponse(?DateTimeImmutable $date_reponse): static
    {
         $this->date_reponse=$date_reponse;
         return $this;
    }

    public function getDateReponse(): ?DateTimeImmutable
    {
        return $this->date_reponse;
    }

    public function __construct()
    {
        $this->date_reponse = new DateTimeImmutable();
        $this->votes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setReponse($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getReponse() === $this) {
                $vote->setReponse(null);
            }
        }

        return $this;
    }
}
