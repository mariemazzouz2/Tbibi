<?php

namespace App\Form;

use App\Entity\Analyse;
use App\Entity\DossierMedical;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AnalyseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('dateanalyse', null, [
                'widget' => 'single_text',
            ])
            ->add('donneesAnalyse', FileType::class, [
                'label' => 'Télécharger un fichier',
                'mapped' => false,  // Empêche Doctrine de gérer ce champ directement
                'required' => false, // Le fichier n'est pas obligatoire
            ])
            ->add('diagnostic')
            ->add('dossier', EntityType::class, [
                'class' => DossierMedical::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Analyse::class,
        ]);
    }
}
