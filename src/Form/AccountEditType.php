<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\NotBlank;



class AccountEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nameaccount', TextType::class ,[
            'constraints' => [
                new NotBlank([
                    'message' => 'Name is required.',
                ]),
            ],
        ])
        
        ->add('balance', NumberType::class, [
            'constraints' => [
                new GreaterThan([
                    'value' => 0,
                    'message' => 'Balance must be a positive number.',
                ]),
                 
                new NotBlank([
                    'message' => 'balance is required.',
                ]),

            ],
        ])
        ->add('description', TextareaType::class,[
            'constraints' => [
                new NotBlank([
                    'message' => 'Description is required.',
                ]),
                
            ],
            'required' => true, // Make the field required even if it's empty
            
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}