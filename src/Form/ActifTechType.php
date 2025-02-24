<?php

namespace App\Form;

use App\Entity\Actif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Employer;

class ActifTechType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('etat', ChoiceType::class, [
                'label' => 'État',
                'placeholder' => 'Sélectionnez un état',
                'choices' => [
                    'Fonctionnel' => 'fonctionnel',
                    'En panne' => 'en panne',
                    'Remplacé' => 'remplacé'
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-select',
                    'aria-label' => 'Sélectionnez un état'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’état est obligatoire'])
                ]
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Actif::class,
        ]);
    }
}
