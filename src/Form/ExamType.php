<?php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\Exam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('dateexam', null, [
                'widget' => 'single_text',
            ])
            ->add('diagnostic')
            ->add('data')
            ->add('dosss', EntityType::class, [
                'class' => DossierMedical::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exam::class,
        ]);
    }
}
