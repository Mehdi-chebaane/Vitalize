<?php

// src/Form/RendezVousType.php

namespace App\Form;

use App\Entity\RendezVous;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateTimeType::class, [
                'label' => 'Meeting Date and Time',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\Callback([$this, 'validateBusinessHours'])
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Appointment Type',
                'choices' => [
                    'In-Person' => 'en_presentiel',
                    'Online' => 'en_ligne',
                ],
                'expanded' => true, // Render as radio buttons
                'multiple' => false, 
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Type cannot be blank.'])
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }

    public function validateBusinessHours($value, $context)
{
    if ($value === null) {
        return; // Skip validation if the value is null
    }
    // Validate if the selected hour is within business hours
    $selectedHour = (int)$value->format('H');
    $selectedMinute = (int)$value->format('i');

    // Check if the selected time is within the business hours (8:00 AM - 6:00 PM)
    if ($selectedHour < 8 || ($selectedHour === 18 && $selectedMinute > 0) || $selectedHour >= 19) {
        $context->buildViolation('Meeting time must be within business hours (8:00 AM - 6:00 PM).')
            ->atPath('date')
            ->addViolation();
    }

    // Check if the selected minute is 0 or 30
    if ($selectedMinute !== 0 && $selectedMinute !== 30) {
        $context->buildViolation('Meeting time must be at every half hour interval.')
            ->atPath('date')
            ->addViolation();
    }
}

}
