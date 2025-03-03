<?php

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\Utilisateur;
use App\Enum\TypeConsultation;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotNull;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('medecin', EntityType::class, [
                'class' => Utilisateur::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%"ROLE_MEDECIN"%')
                        ->orderBy('u.nom', 'ASC');
                },
                'choice_label' => function($doctor) {
                    return sprintf('Dr. %s %s', $doctor->getNom(), $doctor->getPrenom());
                },
                'label' => 'Select Doctor',
                'placeholder' => 'Choose a doctor',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateC', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et Heure',
                'required' => true,
                'html5' => true,
                'input' => 'datetime',
                'empty_data' => null,
                'constraints' => [
                    new NotNull([
                        'message' => 'Please select a date and time for your appointment'
                    ]),
                    new GreaterThan([
                        'value' => new \DateTime(),
                        'message' => 'The appointment date must be in the future'
                    ])
                ],
                'attr' => [
                    'class' => '',
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                    'data-validation-required-message' => 'Please select a date and time',
                    'required' => true
                ],
                'error_bubbling' => false
            ])
            ->add('type', EnumType::class, [
                'class' => TypeConsultation::class,
                'label' => 'Type de consultation',
                'required' => true,
                'choice_label' => function ($choice) {
                    return match($choice) {
                        TypeConsultation::ONLINE => 'Online Consultation',
                        TypeConsultation::ON_SPOT => 'In-person Consultation',
                        default => $choice->value
                    };
                },
                'attr' => ['class' => 'form-control']
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Motif de la consultation',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'minlength' => 10,
                    'maxlength' => 500
                ]
            ]);

        // Only add status field if show_status option is true
        if ($options['show_status']) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => Consultation::STATUS_PENDING,
                    'Confirmé' => Consultation::STATUS_CONFIRMED,
                    'Terminé' => Consultation::STATUS_COMPLETED,
                    'Annulé' => Consultation::STATUS_CANCELLED
                ],
                'attr' => ['class' => 'form-control']
            ]);
        }

        // Set default status to pending on form initialization
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $consultation = $event->getData();
            if (!$consultation || null === $consultation->getId()) {
                $consultation->setStatus(Consultation::STATUS_PENDING);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
            'validation_groups' => ['Default'],
            'show_status' => false, // By default, don't show the status field
        ]);

        $resolver->setAllowedTypes('show_status', 'bool');
    }
}
