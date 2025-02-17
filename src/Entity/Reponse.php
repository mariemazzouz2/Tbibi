<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: "Le contenu de la réponse ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        minMessage: "Le contenu de la réponse doit comporter au moins {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-ZÀ-ÿ][A-Za-z\s\-.,éèêëàùûûïîôôà?]+$/",
        message: "Le contenu  doit commencer par une majuscule et ne contenir que des lettres, des espaces, des tirets, des points, des virgules, des accents et des points d'interrogation."
    )]
    
    private ?string $contenu = null;

    #[ORM\Column]
    private ?DateTimeImmutable $date_reponse;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'reponse', cascade: ['remove'], orphanRemoval: true)]
    private Collection $votes;

    public function __construct()
    {
        $this->date_reponse = new DateTimeImmutable();
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;
        return $this;
    }

    public function getMedecin(): ?Utilisateur
    {
        return $this->medecin;
    }

    public function setMedecin(?Utilisateur $medecin): static
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

    public function getDateReponse(): ?DateTimeImmutable
    {
        return $this->date_reponse;
    }

    public function setDateReponse(?DateTimeImmutable $date_reponse): static
    {
        $this->date_reponse = $date_reponse;
        return $this;
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
            if ($vote->getReponse() === $this) {
                $vote->setReponse(null);
            }
        }
        return $this;
    }
}
