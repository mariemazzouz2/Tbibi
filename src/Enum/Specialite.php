<?php

namespace App\Enum;

enum Specialite: string
{
    case GENERALISTE = 'Médecine Générale';
    case CARDIOLOGUE = 'Cardiologie';
    case DERMATOLOGUE = 'Dermatologie';
    case PEDIATRE = 'Pédiatrie';
    case RADIOLOGUE = 'Radiologie';
    case GYNECOLOGUE = 'Gynécologie';
    case OPHTALMOLOGUE = 'Ophtalmologie';
    case ORL = 'Oto-Rhino-Laryngologie (ORL)';
    case NEUROLOGUE = 'Neurologie';
    case PSYCHIATRE = 'Psychiatrie';
    case UROLOGUE = 'Urologie';
    case GASTROENTEROLOGUE = 'Gastroentérologie';
    case PNEUMOLOGUE = 'Pneumologie';
    case RHUMATOLOGUE = 'Rhumatologie';
    case ENDOCRINOLOGUE = 'Endocrinologie';
    case NEPHROLOGUE = 'Néphrologie';
    case CHIRURGIEN = 'Chirurgie Générale';
    case CHIRURGIEN_CARDIAQUE = 'Chirurgie Cardiaque';
    case CHIRURGIEN_ORTHOPEDIQUE = 'Chirurgie Orthopédique';
    case CHIRURGIEN_PLASTIQUE = 'Chirurgie Plastique';
    case CHIRURGIEN_NEUROLOGIQUE = 'Neurochirurgie';
    case CHIRURGIEN_DENTAIRE = 'Chirurgie Dentaire';
    case MEDECINE_INTERNE = 'Médecine Interne';
    case MEDECINE_NUCLEAIRE = 'Médecine Nucléaire';
    case ANESTHESISTE = 'Anesthésiologie';
    case IMMUNOLOGUE = 'Immunologie';
    case MEDECIN_SPORT = 'Médecine du Sport';
    case MEDECIN_TRAVAIL = 'Médecine du Travail';
    case MEDECINE_LEGALE = 'Médecine Légale';
    case NONE = 'NONE';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
