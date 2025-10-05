<?php

namespace App\Form;

use App\Dto\PetDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', IntegerType::class, ['required' => false])
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, [
                'choices' => ['available' => 'available', 'pending' => 'pending', 'sold' => 'sold']
            ])
            ->add('categoryName', TextType::class, ['required' => false, 'label' => 'Category'])
            ->add('tagsCsv', TextType::class, ['required' => false, 'label' => 'Tags (CSV)'])
            ->add('photoUrls', CollectionType::class, [
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => PetDto::class, 'csrf_protection' => true]);
    }
}
