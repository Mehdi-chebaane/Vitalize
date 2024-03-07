<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;



class EditProfilAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
                
            'attr' => ['class' => 'form-control']
        ])
        ->add('prenom', TextType::class, [
                
            'attr' => ['class' => 'form-control']
        ])
        ->add('email', EmailType::class, [
            
            'attr' => ['class' => 'form-control']
        ])

        
        ->add('avatar', FileType::class, [
            'label' => 'Image',
            'required' => false,
            'data_class' => null, 
            
        ])
          
          
          
            ->add('valider', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => [
                    'class' => 'btn grey',
                    'type' => 'button'
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
