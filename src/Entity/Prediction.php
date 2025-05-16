<?php

namespace App\Entity;

use App\Repository\PredictionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PredictionRepository::class)]
class Prediction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $hypertension = null;

    #[ORM\Column]
    private ?bool $heart_disease = null;

    #[ORM\Column(length: 255)]
    private ?string $smoking_history = null;

    #[ORM\Column]
    private ?float $bmi = null;

    #[ORM\Column]
    private ?float $HbA1c_level = null;

    #[ORM\Column]
    private ?float $blood_glucose_level = null;

    #[ORM\ManyToOne(inversedBy: 'predictions')]
    private ?DossierMedical $dossier = null;

    #[ORM\Column]
    private ?bool $diabete = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isHypertension(): ?bool
    {
        return $this->hypertension;
    }

    public function setHypertension(bool $hypertension): static
    {
        $this->hypertension = $hypertension;

        return $this;
    }

    public function isHeartDisease(): ?bool
    {
        return $this->heart_disease;
    }

    public function setHeartDisease(bool $heart_disease): static
    {
        $this->heart_disease = $heart_disease;

        return $this;
    }

    public function getSmokingHistory(): ?string
    {
        return $this->smoking_history;
    }

    public function setSmokingHistory(string $smoking_history): static
    {
        $this->smoking_history = $smoking_history;

        return $this;
    }

    public function getBmi(): ?float
    {
        return $this->bmi;
    }

    public function setBmi(float $bmi): static
    {
        $this->bmi = $bmi;

        return $this;
    }

    public function getHbA1cLevel(): ?float
    {
        return $this->HbA1c_level;
    }

    public function setHbA1cLevel(float $HbA1c_level): static
    {
        $this->HbA1c_level = $HbA1c_level;

        return $this;
    }

    public function getBloodGlucoseLevel(): ?float
    {
        return $this->blood_glucose_level;
    }

    public function setBloodGlucoseLevel(float $blood_glucose_level): static
    {
        $this->blood_glucose_level = $blood_glucose_level;

        return $this;
    }

    public function getDossier(): ?DossierMedical
    {
        return $this->dossier;
    }

    public function setDossier(?DossierMedical $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function isDiabete(): ?bool
    {
        return $this->diabete;
    }

    public function setDiabete(bool $diabete): static
    {
        $this->diabete = $diabete;

        return $this;
    }
}
