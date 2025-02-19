<?php

namespace App\Form;

use App\Entity\CategorieEv;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le titre ne doit pas dépasser 255 caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description est requise.']),
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner la date de début.']),
                ],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner la date de fin.']),
                ],
            ])
            ->add('lieu', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu est obligatoire.']),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un statut.']),
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieEv::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une catégorie.']),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Event Image (JPEG/PNG file)',
                'mapped' => false, // Ce champ n'est pas directement lié à une propriété de l'entité
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image JPEG ou PNG valide.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
