<?php

namespace App\Entity;

use App\Repository\ExamRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExamRepository::class)]
class Exam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateexam = null;

    #[ORM\Column(length: 255)]
    private ?string $diagnostic = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $data = [];

    #[ORM\ManyToOne(inversedBy: 'exams')]
    private ?DossierMedical $dosss = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        assert(!empty($type), 'Le type ne peut pas être vide.');
        $this->type = $type;
        return $this;
    }

    public function getDateexam(): ?\DateTimeInterface
    {
        return $this->dateexam;
    }

    public function setDateexam(\DateTimeInterface $dateexam): static
    {
        assert($dateexam <= new \DateTime(), 'La date de l'examen ne peut pas être dans le futur.');
        $this->dateexam = $dateexam;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(string $diagnostic): static
    {
        assert(!empty($diagnostic), 'Le diagnostic ne peut pas être vide.');
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        assert(is_array($data), 'Les données doivent être un tableau.');
        $this->data = $data;
        return $this;
    }

    public function getDosss(): ?DossierMedical
    {
        return $this->dosss;
    }

    public function setDosss(?DossierMedical $dosss): static
    {
        $this->dosss = $dosss;
        return $this;
    }
}
