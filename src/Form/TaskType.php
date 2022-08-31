<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{TextType, ButtonType, EmailType, HiddenType, PasswordType, TextareaType, SubmitType, NumberType, DateType, MoneyType, BirthdayType, ChoiceType, DateTimeType};


class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nameTask')
        ->add('descriptionTask',)
        ->add('dueDateTask',DateTimeType::class, ['widget'=>'single_text'])
        ->add('priorityTask',ChoiceType::class,['choices'  => [
            'Haut' => 'Haut',
            'Moyen' => 'Moyen',
            'Bas' => 'Bas',
        ]])
        ->add('category')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
