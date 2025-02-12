<?php

namespace App\Form;

use App\Entity\CategorieEv;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Enum\Statut;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class Evenement1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titre')
        ->add('description')
        ->add('dateDebut', null, [
            'widget' => 'single_text',
        ])
        ->add('dateFin', null, [
            'widget' => 'single_text',
        ])
        ->add('lieu')
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
        ])        ->add('categorie', EntityType::class, [
            'class' => CategorieEv::class,
            'choice_label' => 'id',
        ])
        ->add('image', FileType::class, [
            'label' => 'Image de l\'événement',
            'required' => false,
            'mapped' => false, // important pour gérer manuellement l'upload
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF).',
                ])
            ]
        ]);
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
    
}
