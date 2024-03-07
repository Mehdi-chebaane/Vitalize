<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints  as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;







class ReclamationType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }  
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
{   
    $builder
    
    ->add('type', ChoiceType::class, [
        'choices' => [
            'Plainte' => 'Plainte',
            'Demande information' => 'Demande information',
            'Suggestion' => 'Suggestion',
        ],
        'placeholder' => 'Type',  
        'constraints' => [
            new Assert\NotBlank(),
        ],
    ])
    
    ->add('medecin', ChoiceType::class, [
        'choices' => $this->getMedecinChoices(),
        'placeholder' => 'Médecin',  
        'constraints' => [
            new Assert\NotBlank(),
        ],
    ])
    
            ->add('sujet', TextType::class, [
                'attr' => [
                    'placeholder' => 'Sujet',  ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'min' => 2,
                            'max' => 20,
                            'minMessage' => 'The sujet must be at least {{ limit }} characters long',
                            'maxMessage' => 'The sujet cannot be longer than {{ limit }} characters',
                        ]),
                    ],    
                   
                ])


            ->add('description', TextareaType::class , [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'min' => 3,
                            'max' => 100,
                            'minMessage' => 'The description must be at least {{ limit }} characters long',
                            'maxMessage' => 'The description cannot be longer than {{ limit }} characters',
                        ]), 
                  ],
                ])
                

            ->add('file', FileType::class, [
                'required' => false,
                'data_class' => null,
                'label' => 'Veuillez insèrer des documents liés à votre réclamation',
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('submitButton', SubmitType::class, [
                'label' => 'Send Reclamation',
            ]);
        
    }

    private function getMedecinChoices(): array
    {
        $medecinUsers = $this->entityManager->getRepository(Users::class)->findByRole('ROLE_MEDECIN');
    
        $choices = [];
        foreach ($medecinUsers as $user) {
            $choices[$user->getNom()] = $user->getId(); // Using full name as key and ID as value
        }
    
        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
