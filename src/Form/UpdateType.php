<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class UpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            
            ->add('incometype',ChoiceType::class, [
                'choices'  => [
                    'Fixed' => true,
                    'Flexible' => false
                ],])
            ->add('budgettype',ChoiceType::class, [
                'choices'  => [
                    'Fixed' => true,
                    'Flexible' => false
                ],])
            ->add('rent',ChoiceType::class,[
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ])
            ->add('debt',ChoiceType::class,[
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ])
            ->add('transport',ChoiceType::class, [
                'choices'  => [
                    'Walking' => true,
                    'Car' => false,
                    'Public Transport' => false
                ],])
            

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
