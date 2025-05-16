<?php

namespace App\Form;

use App\Entity\Ordonnance;
use App\Entity\Consultation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consultation', EntityType::class, [
                'class' => Consultation::class,
                'choice_label' => function (Consultation $consultation) {
                    return 'Consultation #' . $consultation->getId() . ' - ' . $consultation->getDateC()->format('Y-m-d');
                },
                'label' => 'Consultation associée',
                'placeholder' => 'Sélectionnez une consultation',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de l’Ordonnance',
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Détails de l’ordonnance...'],
            ])
            ->add('signature', TextType::class, [
                'label' => 'Signature du Médecin',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Signature du médecin'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ordonnance::class,
        ]);
    }
}
