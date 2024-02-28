<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotNull;
class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_activite', ChoiceType::class, [
                'choices' => [
                    'Activité brise glace' => 'Activité brise glace',
                    'Conférence éducationnelle' => 'Conférence éducationnelle',
                    'Plateau Q/A' => 'Plateau Q/A',
                    'Sortie collective' => 'Sortie collective',
                    'Cercle de discussion' => 'Cercle de discussion',
                ],
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('Description')
            ->add('idevenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => 'nom',
                'label' => "Nom de l'evenement",
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('hours', IntegerType::class, [
                'required' => false,
                'label' => "Heures :",
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('minutes', IntegerType::class, [
                'required' => false,
                'label' => "Minutes :",
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('imageAct', FileType::class, [
                'required' => false, 
                'mapped' => false, 
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG)',
                    ]),new NotNull([
                        'message' => 'Veuillez insérer une image.',
                    ]),
                ],
            ]);            

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $hours = $form->get('hours')->getData();
            $minutes = $form->get('minutes')->getData();
            $totalMinutes = $hours * 60 + $minutes;
            $event->getData()->setDuree($totalMinutes);
        });;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }
}
