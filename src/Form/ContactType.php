<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'attr' =>[
                    'class' =>'form-control',
                    
                    
        

                ],
                'label' =>'Nom'
            ])
            ->add('email',EmailType::class,[
                'attr' =>[
                    'class' =>'form-control',

                ],
                'label' =>'E-mail'
            ])
            ->add('message',TextAreaType::class,[
                'attr' =>[
                    'class' =>'form-control',

                ],
                'label' =>'Message'
            ])
            ->add('image', FileType::class, [
                'label' => 'VerificationMedecin',
                'required' => false,
                'data_class' => null,
                 'attr' =>[
                    'class' =>'form-control',

                ]
                
            ])
            ->add('valider', SubmitType::class,[
                'attr' =>[
                    'class' =>'form-control',

                ],
                'label' =>'VerifierMedecin'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
