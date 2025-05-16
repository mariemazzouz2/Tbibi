<?php

namespace App\Form;

use App\Entity\CategorieEv;
use App\Entity\Evenement;
use App\Enum\Statut;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class Evenement1Type extends AbstractType
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
                'required' => true, // Assurez-vous que ce champ est requis
                'label' => 'Date de début',
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'required' => true, // Assurez-vous que ce champ est requis
                'label' => 'Date de fin',
            ])
          
            ->add('lieu', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu est obligatoire.']),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => Statut::EN_ATTENTE,
                    'Confirmé' => Statut::CONFIRME,
                    'Annulé' => Statut::ANNULE,
                    'Terminé' => Statut::TERMINE,
                ],
                'label' => 'Statut de l\'événement',
                'placeholder' => 'Sélectionnez un statut',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un statut.']),
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieEv::class,
                'choice_label' => 'nom',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une catégorie.']),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'événement',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF).',
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
