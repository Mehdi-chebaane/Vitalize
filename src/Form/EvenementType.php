<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('date')
            ->add('localisation')
            ->add('capacite', IntegerType::class)
            ->add('organisateur')
            ->add('description')
            ->add('imageEve', FileType::class, [
                'required' => false, 
                'mapped' => false, 
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG)',
                    ]),new NotNull([
                        'message' => 'Veuillez insérer une image',
                    ]),
                ],
            ]); 

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
