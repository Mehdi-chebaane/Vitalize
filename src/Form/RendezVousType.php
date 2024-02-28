<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\DoctorAv; // Fixed typo here
use App\Form\DoctorAvType; // Fixed typo here
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'hours' => range(8, 18),
                'minutes' => range(0, 45, 30),
                'html5' => true,
                'years' => range(date('Y'), date('Y')), // Set the range to the current year only
            ])
            ->add('lien')
           
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class, // Corrected data class
        ]);
    }
}
