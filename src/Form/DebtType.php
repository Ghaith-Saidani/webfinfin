<?php

namespace App\Form;

use App\Entity\Debt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DebtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', null, [
                'attr' => ['placeholder' => 'Enter amount']
            ])
            ->add('paymentdate', DateType::class, [
                'label' => 'Payment Date:',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Select payment date']
            ])
            ->add('amounttopay', null, [
                'attr' => ['placeholder' => 'Enter amount to pay']
            ])
            ->add('interestrate', null, [
                'attr' => ['placeholder' => 'Enter interest rate']
            ])
            ->add('creationdate', DateType::class, [
                'label' => 'Creation Date:',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Select creation date']
            ])
            ->add('type', null, [
                'attr' => ['placeholder' => 'Select type']
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Debt::class,
        ]);
    }
}