<?php

namespace App\Form;

use App\Entity\Todolist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class TodolistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('idtodo') when needed
            ->add('titletodo')
            ->add('statustodo', ChoiceType::class, [
                'choices' => [
                    'Done' => 'done',
                    'Not Done' => 'not done',
                ],
                'placeholder' => 'Choose status', 
            ])
            // ->add('progress')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Todolist::class,
        ]);
    }
}
