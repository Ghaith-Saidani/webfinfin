<?php

namespace App\Form;

use App\Entity\Debtcategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DebtcategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('NameDebt', TextType::class, [
                'label' => 'NameDebt',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter Name Debt'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Debtcategory::class,
        ]);
    }
}