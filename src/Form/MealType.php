<?php

namespace App\Form;

use App\Entity\Meal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomRepas')
            ->add('ingredients')
            ->add('recette')
            ->add('typeRepas', ChoiceType::class, [
                'choices' => [
                    'Breakfast' => 'Breakfast',
                    'Lunch' => 'Lunch',
                    'Dinner' => 'Dinner'
                ],
                ])
            ->add('image')
            ->add('nombrePersonnes')
            ->add('dureePreparation')
            ->add('prix')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
        ]);
    }
}
