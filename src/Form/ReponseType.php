<?php

namespace App\Form;

use App\Entity\Reponse;
use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('message', TextareaType::class , [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'The Message must be at least {{ limit }} characters long',
                        'maxMessage' => 'The Message cannot be longer than {{ limit }} characters',
                    ]), 
              ],
            ])

            ->add('submitButton', SubmitType::class, [
                'label' => 'Send Reponse',
            ]);
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                'data_class' => Reponse::class ,           
        ]);
    }
}
