<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Todolist;
use App\Entity\Subcategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('descriptiontask', null, [
                'label' => 'Description:',
                'attr' => ['class' => 'form-control']
            ])           
            ->add('priority', ChoiceType::class, [
                'label' => 'Priority:',
                'choices' => [
                    'Low' => 'low',
                    'Medium' => 'medium',
                    'High' => 'high',
                ],
                'placeholder' => 'Choose priority',
                'attr' => ['class' => 'form-control']
            ])
            ->add('duedate', DateTimeType::class, [
                'label' => 'Due Date:',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control']
            ])
            
            
            ->add('statustask', ChoiceType::class, [
                'label' => 'Status:',
                'choices' => [
                    'Done' => 'done',
                    'Not Done' => 'not done',
                ],
                'placeholder' => 'Choose status',
                'attr' => ['class' => 'form-control']
            ])
            ->add('idtodo', EntityType::class, [
                'label' => 'Todo List:',
                'class' => Todolist::class,
                'choice_label' => 'titletodo',
                'placeholder' => 'Choose Todo',
                'mapped' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('idsubcategory', EntityType::class, [
                'label' => 'Subcategory:',
                'class' => Subcategory::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose Subcategory',
                'mapped' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('mtapayer', null, [
                'label' => 'Amount To Pay:',
                'attr' => ['class' => 'form-control', 'readonly' => true]
            ]) 
            ->add('creationdate', DateTimeType::class, [
                'label' => 'Creation Date:',
                'widget' => 'single_text',
                'html5' => false,
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control datetimepicker-input',
                    'data-toggle' => 'datetimepicker',
                    'readonly' => true,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
