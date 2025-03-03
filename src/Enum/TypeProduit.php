<?php

namespace App\Enum;

enum TypeProduit: string
{
    case PRODUIT_ORTHOPEDIQUE = 'produit_orthopedique';
    case PRODUIT_PARAPHARMACIE = 'produit_parapharmacie';
    case MATERIEL_MEDICAL = 'materiel_medical';
    case SUPPLEMENT = 'supplement';
    case AIDE_MOBILITE = 'aide_mobilite';
    case PRODUIT_HYGIENE = 'produit_hygiene';
    case AUTRE = 'autre';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRODUIT_ORTHOPEDIQUE => 'Produit Orthopédique',
            self::PRODUIT_PARAPHARMACIE => 'Produit de Parapharmacie',
            self::MATERIEL_MEDICAL => 'Matériel Médical',
            self::SUPPLEMENT => 'Complément Alimentaire',
            self::AIDE_MOBILITE => 'Équipement d’Aide à la Mobilité',
            self::PRODUIT_HYGIENE => 'Produit d’Hygiène',
            self::AUTRE => 'Autre',
        };
    }
}
