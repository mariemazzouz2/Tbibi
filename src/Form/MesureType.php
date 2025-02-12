<?php

namespace App\Form;

use App\Entity\Dossiermedical;
use App\Entity\Mesure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MesureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('datemesure', null, [
                'widget' => 'single_text',
            ])
            ->add('mesure')
            ->add('unité')
            ->add('doss', EntityType::class, [
                'class' => Dossiermedical::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mesure::class,
        ]);
    }
}
