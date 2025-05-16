<?php

namespace App\Enum;

enum Statut: string
{
    case EN_ATTENTE = 'En attente';
    case CONFIRME = 'Confirmé';
    case ANNULE = 'Annulé';
    case TERMINE = 'Terminé';
    case INACTIF = 'inactif'; // Conserver l'ajout précédent
    case ACTIF = 'actif';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
