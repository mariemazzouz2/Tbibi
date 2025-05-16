<?php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\Prediction;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PredictionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('hypertension', CheckboxType::class, [
            'label' => 'Hypertension',
            'required' => false,
        ])
            ->add('heart_disease', CheckboxType::class, [
                'label' => 'Heart Disease',
                'required' => false,
            ])
            ->add('smokingHistory', ChoiceType::class, [
                'choices' => [
                    'No Info' => 'no_info',
                    'Current' => 'current',
                    'Ever' => 'ever',
                    'Never' => 'never',
                    'Former' => 'former',
                    'Not Current' => 'not_current',
                ],
                'required' => false,
            ])
            ->add('bmi', NumberType::class, [
                'label' => 'BMI'
            ])
            ->add('HbA1c_level', NumberType::class, [
                'label' => 'HbA1c Level'
            ])
            ->add('blood_glucose_level', NumberType::class, [
                'label' => 'Niveau de glucose sanguin'
            ])
            ->add('dossier', EntityType::class, [
                'class' => DossierMedical::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prediction::class,
        ]);
    }
}
