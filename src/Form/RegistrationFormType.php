<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        
            ->add('email',EmailType::class,[
                'attr' =>[
                    'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1',
                    

                ],
                'label' =>'E-mail'
            ])
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
    
                ],
                'label' => 'Nom',
                
            ])
            ->add('prenom',TextType::class,[
                'attr' =>[
                    'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
                ],
                'label' =>'Prenom'
            ])
      
            ->add('tel',TextType::class,[
                'attr' =>[
                    'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
                ],
                'label' =>'Telephone'
            ])
            ->add('numCnam',TextType::class,[
                'attr' =>[
                    'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
                ],
                'label' =>'Numero Cnam(Patient)',
                'required' => false,
            ])
            ->add('adresse',TextType::class,[
                'attr' =>[
                    'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
                ],
                'label' =>'Adresse',
                'required' => false,
            ])
            ->add('verif', FileType::class, [
                'label' => 'VerificationMedecin',
                'required' => false,
                'data_class' => null,
                 'attr' =>[
                    'class' =>'form-control',

                ] , 'row_attr' => ['style' => 'margin-top: 18px;'
            ], 
                
            ])

            ->add('consent', CheckboxType::class , [
                                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Accept les conditions',
                    ]),
                    
                ],
                
                'label' =>' j\'accepte toutes les conditions  ',
                'label_attr' => [
                    'style' => 'margin-right: 10px;', 
                ],  'row_attr' => ['style' => 'margin-top: 18px;'
            ], 
            ])
           




            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
               
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr' => ['autocomplete' => 'new-password', 'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
                ]
                ],
                
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'attr' => ['autocomplete' => 'new-password', 'class' =>'form-control ps-2 fs-7 border-start-0 form-control-lg inbg mb-0',
                    'aria-describedby'=>'basic-addon1'
                     ],
                    'row_attr' => ['style' => 'margin-top: 18px;'
                ], 

                ],
                
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                
               
            ])
            ->setAttributes([
                
            ]);
            
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
