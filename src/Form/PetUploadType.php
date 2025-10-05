<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{TextType, FileType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class PetUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('additionalMetadata', TextType::class, [
                'required' => false, 'label' => 'Additional metadata'
            ])
            ->add('file', FileType::class, [
                'label' => 'Image file',
                'mapped' => false,
                'constraints' => [new NotBlank(), new File(maxSize: '5M')]
            ]);
    }
}
