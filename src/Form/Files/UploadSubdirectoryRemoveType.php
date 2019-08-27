<?php

namespace App\Form\Files;

use App\Controller\Files\FileUploadController;
use App\Form\Type\UploadrecursiveoptionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadSubdirectoryRemoveType extends AbstractType
{
    const OPTION_SUBDIRECTORIES = 'subdirectories';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(FileUploadController::KEY_UPLOAD_TYPE, ChoiceType::class, [
                'choices' => FileUploadController::UPLOAD_TYPES,
                'attr'    => [
                    'class'                        => 'form-control listFilterer',
                    'data-dependent-list-selector' => '#upload_subdirectory_remove_subdirectory_name'
                ]
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_NAME, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
            ])
            ->add('submit', SubmitType::class, [

            ]);

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_NAME)->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);

    }
}
