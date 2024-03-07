<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Publication;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $parentId = $options['parentId'] ?? null;
    $builder
        ->add('contenu')
        ->add('id_user', EntityType::class, [
            'class' => Users::class,
            'choice_label' => 'nom', 
        ])
        ->add('id_pub', EntityType::class, [
            'class' => Publication::class,
            'choice_label' => 'titre', 
        ])
        ->add('parentComment', HiddenType::class, [
            'data' => $parentId,
        ]);
    
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
            'parentId' => null, // Add parentId option here
        ]);
    }
}
