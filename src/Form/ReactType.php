<?php

namespace App\Form;

use App\Entity\React;
use App\Entity\Users;
use App\Entity\Publication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_user', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'nom', // Assuming User entity has a 'username' property
            ])
            ->add('id_pub', EntityType::class, [
                'class' => Publication::class,
                'choice_label' => 'titre', // Assuming Publication entity has a 'title' property
            ])
            ->add('likeCount')
            ->add('dislikeCount');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => React::class,
        ]);
    }
}
