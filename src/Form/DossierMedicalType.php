<?php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DossierMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('fichier', FileType::class, [
                'label' => 'Télécharger un fichier',
                'mapped' => false,  // Indique que ce champ n'est pas lié directement à l'entité
                'required' => false, // Permet de ne pas forcer l'upload à chaque soumission
            ])
            ->add('unite', ChoiceType::class, [
                'choices' => [
                    'Millimètres de mercure (mmHg)' => 'mmHg',
                    'Milligrammes par décilitre (mg/dL)' => 'mg/dL',
                    'Millimoles par litre (mmol/L)' => 'mmol/L',
                    'Kilogrammes (kg)' => 'kg',
                    'Livres (lb)' => 'lb',
                    'Centimètres (cm)' => 'cm',
                    'Mètres (m)' => 'm',
                    'Degrés Celsius (°C)' => 'Celsius',
                    'Degrés Fahrenheit (°F)' => 'Fahrenheit',
                    'Battements par minute (bpm)' => 'bpm',
                    'Pourcentage (%)' => 'percentage',
                    'Respiration par minute (rpm)' => 'rpm',
                ],
                'label' => 'Unité de mesure',
            ])
            ->add('mesure')
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMedical::class,
        ]);
    }
}
