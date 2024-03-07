<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
         ->add('methodePaiement', ChoiceType::class, [
            'choices' => [
                'À la livraison' => 'à la livraison',
                'E-paiement' => 'e-paiement',
            ],
            'expanded' => true, // Pour afficher les boutons radio au lieu d'une liste déroulante
            'label' => 'Méthode de paiement',
            'required' => true,
        ])
            
            
            ->add('instructionSpeciale')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}