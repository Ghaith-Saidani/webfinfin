<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Repository\PayeeRepository;
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

class IncomeTransactionType extends AbstractType
{
    private $payeeRepository;

    public function __construct(PayeeRepository $payeeRepository)
    {
        $this->payeeRepository = $payeeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
        ->add('description', TextareaType::class,[
            'constraints' => [
                new NotBlank([
                    'message' => 'Description is required.',
                ]),
            ],
        ])
            ->add('amount', NumberType::class, [
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'Amount must be a positive number.',
                    ]),
                     
                    new NotBlank([
                        'message' => 'Amount is required.',
                    ]),
    
                ],
            ])
            
            ->add('idpayee', ChoiceType::class, [
                'label' => 'Payee',
                'choices' => $this->payeeRepository->findByWalletId($options['wallet_id']),
                'choice_label' => 'namepayee', 
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'wallet_id' => null,
        ]);
    }
}
