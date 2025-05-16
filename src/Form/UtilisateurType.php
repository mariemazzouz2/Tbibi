<?php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Enum\Specialite;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $isMedecin = $options['is_medecin'];

    $builder
        ->add('nom')
        ->add('prenom')
        ->add('email')
        ->add('password', PasswordType::class)
        ->add('telephone')
        ->add('adresse')
        ->add('dateNaissance', DateType::class, [
            'widget' => 'single_text',
        ])
        ->add('sexe', ChoiceType::class, [
            'choices' => [
                'Homme' => 'Homme',
                'Femme' => 'Femme',
            ],
        ])
        ->add('image', FileType::class, [
            'mapped' => false,
            'required' => false,
        ]);

        if ($isMedecin) {
            $builder->add('specialite', ChoiceType::class, [
                'label' => 'Spécialité',
                'choices' => array_combine(
    array_map(fn (Specialite $specialite) => $specialite->value, Specialite::cases()), 
    Specialite::cases()
), // Utiliser Specialite::cases() pour obtenir les objets enum
                'required' => true,
                'choice_value' => fn (?Specialite $specialite) => $specialite?->value, // Conversion pour éviter l'erreur
                'choice_label' => fn (Specialite $specialite) => $specialite->value, // Afficher le label correctement
            ]);
            
    } else {
        $builder
            ->add('taille')
            ->add('poids');
    }
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Utilisateur::class,
        'is_medecin' => false, // Défaut : patient
    ]);
}
}
