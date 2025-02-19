<?php
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyseRepository::class)]
class Analyse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type d'analyse est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le type d'analyse doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le type d'analyse ne doit pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Choice(choices: ["Sanguin", "Urinaire", "Radiologie"], message: "Choisissez un type d'analyse valide.")]
    private ?string $type = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date de l'analyse est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "La date doit être une date valide.")]
    #[Assert\LessThanOrEqual("today", message: "La date d'analyse ne peut pas être dans le futur.")]
    private ?\DateTimeInterface $dateanalyse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Les données de l'analyse sont obligatoires.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Les données de l'analyse doivent contenir au moins {{ limit }} caractères.",
        maxMessage: "Les données de l'analyse ne doivent pas dépasser {{ limit }} caractères."
    )]
    private ?string $donneesAnalyse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le diagnostic est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le diagnostic doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le diagnostic ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $diagnostic = null;

    #[ORM\ManyToOne(inversedBy: 'analyses')]
    #[Assert\NotNull(message: "Le dossier médical est obligatoire.")]
    private ?DossierMedical $dossier = null;

    // Getters & Setters...
}
