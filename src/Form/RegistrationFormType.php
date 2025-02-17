<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Enum\Specialite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un email.'])],
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un mot de passe.'])],
            ])
            ->add('nom', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('prenom', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('telephone', NumberType::class, ['constraints' => [new NotBlank()]])
            ->add('adresse', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => ['Homme' => 'Homme', 'Femme' => 'Femme'],
                'constraints' => [new NotBlank()],
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false, // Ce champ n'est pas directement mappé à l'entité
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Image([
                        'maxSize' => '2M', // Limite à 2 Mo
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG/PNG).',
                    ])
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Médecin' => 'ROLE_MEDECIN',
                    'Patient' => 'ROLE_PATIENT',
                ],
                'mapped' => false,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('specialite', ChoiceType::class, [
                'choices' => Specialite::cases(),
                'choice_label' => fn (Specialite $s) => $s->value, // Affiche les noms lisibles
                'choice_value' => fn (?Specialite $s) => $s?->value, // Stocke la valeur Enum
                'required' => false,
                'placeholder' => 'Sélectionnez une spécialité', // Permet d'avoir une valeur null par défaut
            ])
            ->add('diplome', FileType::class, [
                'label' => 'Diplôme (PDF ou Image)',
                'mapped' => false, // Pour gérer l'upload manuellement
                'required' => false,
            ])
            ->add('taille', NumberType::class, [
                'required' => false,
            ])
            ->add('poids', NumberType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
