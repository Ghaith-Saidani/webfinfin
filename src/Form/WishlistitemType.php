<?php

namespace App\Form;

use App\Entity\Wishlistitem;
use App\Entity\Wishlist;
use App\Entity\Itemcategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; 
class WishlistitemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('namewishlistitem')
            ->add('price')
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'High' => 'High',
                    'Medium' => 'Medium',
                    'Low' => 'Low',
                     // Optional placeholder text

                ],'placeholder' => 'Choose a priority',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Not Started' => 'NOT_STARTED',
                    'In Progress' => 'IN_PROGRESS',
                    'Done' => 'DONE',
                ],'placeholder' => 'Choose a status',

            ])
            ->add('iditemcategory', EntityType::class, [
                'class' => Itemcategory::class,
                'choice_label' => 'nameitemcategory', // Assuming 'name' is the property to display in the dropdown
                'placeholder' => 'Choose a category', // Optional placeholder text
            ])
            ->add('idwishlist', EntityType::class, [
                'class' => Wishlist::class,
                'choice_label' => 'namewishlist', // Assuming 'name' is the property to display in the dropdown
                'placeholder' => 'Choose a wishlist', // Optional placeholder text
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wishlistitem::class,
        ]);
    }
}
