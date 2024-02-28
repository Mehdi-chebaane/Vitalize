<?php

namespace App\Form;

use App\Entity\Fichepatient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FichepatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight')
            ->add('muscle_mass')
            ->add('height')
            ->add('allergies')
            ->add('illnesses')
            ->add('breakfast')
            ->add('midday')
            ->add('dinner')
            ->add('snacks')
            ->add('calories')
            ->add('other')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fichepatient::class,
        ]);
    }
}
