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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\Specialite;
use Symfony\Component\Validator\Constraints\NotNull;


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
                ],
                'required' => true,
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez votre question...',
                    'class' => 'form-control',
                ],
                'required' => true,
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
                
                ])
            
                ->add('visible', CheckboxType::class, [
                    'label' => 'Visibilité',
                    'required' => true, 
                    'attr' => [
                        'class' => 'form-check-input',
                    ],
                    'constraints' => [
                        new NotNull([
                            'message' => 'Veuillez spécifier la visibilité.',
                        ])
                    ]
                ])
                
            ->add('image', FileType::class, [
                'label' => 'Image (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG/PNG)',
                    ]),
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
