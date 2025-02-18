<?php

namespace App\Form;

use App\Entity\CategorieEv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
class CategorieEvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', null, [
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                new Assert\Length([
                    'min' => 3,
                    'max' => 255,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                ]),
            ]
        ])
        ->add('description', null, [
            'constraints' => [
                new Assert\NotBlank(['message' => 'La description ne peut pas être vide.']),
                new Assert\Length([
                    'min' => 5,
                    'max' => 255,
                    'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                ]),
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategorieEv::class,
        ]);
    }
}
