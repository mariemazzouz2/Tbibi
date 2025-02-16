<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\Specialite;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la question',
                'attr' => [
                    'placeholder' => 'Entrez le titre...',
                    'class' => 'form-control',
                    'pattern' => '^[A-Za-z\s\-]+$', // Exemple de pattern pour les titres
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre est obligatoire.'
                    ]),
                    new Regex([
                        'pattern' => '/^[A-Za-z\s\-]+$/',
                        'message' => 'Le titre ne doit contenir que des lettres, des espaces et des tirets.'
                    ])
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre question...',
                    'class' => 'form-control',
                    'pattern' => '^[\w\s\.,!?\'-]+$', // Exemple de pattern pour le contenu
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description détaillée est obligatoire.'
                    ]),
                    new Regex([
                        'pattern' => '/^[\w\s\.,!?\'-]+$/',
                        'message' => 'Le contenu ne doit contenir que des lettres, chiffres et quelques signes de ponctuation.'
                    ])
                ]
            ])
            ->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité médicale',
                'choices' => array_combine(
                    array_map(fn($case) => $case->value, Specialite::cases()),
                    Specialite::cases()
                ),
                'placeholder' => 'Sélectionnez une spécialité',
                'choice_value' => fn(?Specialite $enum) => $enum?->value,
                'choice_label' => fn(Specialite $enum) => $enum->value,
                'attr' => ['class' => 'form-select selectpicker', 'data-live-search' => 'true'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La spécialité médicale est obligatoire.'
                    ])
                ]
            ])
            ->add('visible', CheckboxType::class, [
                'label' => 'Visibilité',
                'required' => false, // Le checkbox peut être non coché
                'attr' => [
                    'class' => 'form-check-input', // Classe pour le style du checkbox
                ],
                'label_attr' => [
                    'class' => 'form-check-label' // Classe pour le label
                ],
                'help' => 'Cochez cette case pour rendre la question visible.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez spécifier la visibilité.'
                    ])
                ]
            ])
            
            ->add('image', FileType::class, [
                'label' => 'Image (optionnel)',
                'mapped' => false, // Not mapped directly to the entity
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG/PNG)',
                    ]),
                    // Optionally add NotBlank constraint if the field is required
                    new NotBlank([
                        'message' => 'Veuillez télécharger une image.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
