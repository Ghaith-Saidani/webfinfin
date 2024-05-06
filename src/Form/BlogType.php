<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content', TextareaType::class, [
                'attr' => ['placeholder' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque quam neque, auctor vitae consequat a, condimentum in ante. Duis dictum iaculis malesuada. Vivamus ultrices erat nibh, id pretium ante mattis et. Donec malesuada elit at semper suscipit. Etiam sed lacus sit amet nisi aliquet tempus.'], // Add placeholder here
            ])
            ->add('datePublished')
            ->add('imageFile', FileType::class, [
                'label' => 'Blog Image', // Add label for the file upload field
                'required' => true, // Set to false if the image is optional
                'mapped' => false, // Set to false to prevent mapping to entity property
            ])
            ->add('tags');
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
