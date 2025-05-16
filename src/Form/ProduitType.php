<?php
namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Image;


class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du produit est obligatoire.']),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire.']),
                    new Positive(['message' => 'Le prix doit être positif.']),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de produit',
                'choices' => [
                    'Vitamine' => 'vitamine',
                    'Complément Alimentaire' => 'complement_alimentaire',
                    'Cosmétique' => 'cosmetique',
                    'Hygiène' => 'hygiene',
                    'Produit Orthopédique' => 'produit_orthopedique',
                    'Produit de Parapharmacie' => 'produit_parapharmacie',
                    'Matériel Médical' => 'materiel_medical',
                    'Équipement d"Aide à la Mobilité' => 'aide_mobilite',
                    'Produit d"Hygiène' => 'produit_hygiene',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez un type',
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire.']),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => $options['require_image'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    ])
                ],
            ])
            ->add('commande', EntityType::class, [
                'class' => Commande::class,
                'label' => 'Commande associée (optionnel)',
                'choice_label' => 'id',
                'required' => false,
                'placeholder' => 'Aucune commande',
                'attr' => ['class' => 'form-control']
            ]);
            
            // Add form event listener for image field validation
            $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
                $produit = $event->getData();
                $form = $event->getForm();
                
                // Check if this is a new product (no ID) and requires an image
                if ($options['require_image'] && null === $produit->getId() && !$form->get('imageFile')->getData()) {
                    $form->get('imageFile')->addError(new \Symfony\Component\Form\FormError('L\'image est obligatoire pour un nouveau produit.'));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'require_image' => true, // By default, require an image for new products
        ]);
        
        $resolver->setAllowedTypes('require_image', 'bool');
    }
}
