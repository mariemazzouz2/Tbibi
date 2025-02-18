<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\TypeConsultation;
use Doctrine\DBAL\Types\Type;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: TypeConsultation::class)]
    private ?TypeConsultation $type = null;

    #[ORM\Column(length: 255)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateC = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $meetLink = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'consultationsMedecin')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $medecin = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'consultationsPatient')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $patient = null;

    #[ORM\OneToOne(mappedBy: 'consultation', targetEntity: Ordonnance::class, cascade: ['persist', 'remove'])]
    private ?Ordonnance $ordonnance = null;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeConsultation
    {
        return $this->type;
    }

    public function setType(TypeConsultation $type): self
    {
        $this->type = $type;
        return $this;
    }
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDateC(): ?\DateTimeInterface
    {
        return $this->dateC;
    }

    public function setDateC(\DateTimeInterface $dateC): static
    {
        $this->dateC = $dateC;

        return $this;
    }

    public function getMeetLink(): ?string
    {
        return $this->meetLink;
    }

    public function setMeetLink(?string $meetLink): self
    {
        $this->meetLink = $meetLink;
        return $this;
    }

    public function getMedecin(): ?Utilisateur
    {
        return $this->medecin;
    }

    public function setMedecin(Utilisateur $medecin): static
    {
        $this->medecin = $medecin;
        return $this;
    }

    public function getPatient(): ?Utilisateur
    {
        return $this->patient;
    }

    public function setPatient(Utilisateur $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    public function getOrdonnance(): ?Ordonnance
    {
        return $this->ordonnance;
    }

    public function setOrdonnance(Ordonnance $ordonnance): static
    {
        $this->ordonnance = $ordonnance;
        return $this;
    }
}
