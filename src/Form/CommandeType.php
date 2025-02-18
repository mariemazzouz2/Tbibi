<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('dateCommande', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date de Commande',
                'required' => true,
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'En cours',
                    'Expédiée' => 'Expédiée',
                    'Livrée' => 'Livrée',
                    'Annulée' => 'Annulée'
                ],
                'expanded' => false,  // Affichage sous forme de dropdown
                'multiple' => false,  // Un seul choix possible
                'placeholder' => 'Sélectionnez un statut',
                'attr' => ['class' => 'form-control'],
                'label' => 'Statut',
                'required' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom', // Afficher le nom de l'utilisateur au lieu de l'ID
                'placeholder' => 'Sélectionnez un utilisateur', // Option vide par défaut
                'attr' => ['class' => 'form-control'],
                'label' => 'Utilisateur',
                'required' => true,
            ])
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'multiple' => true, // Permet de sélectionner plusieurs produits
                'expanded' => false, // Change à true si tu veux des cases à cocher
                'label' => 'Produits commandés',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
