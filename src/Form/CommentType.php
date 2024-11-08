<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Conference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('author', null, [
                'label'=> 'your name',
            ])
            ->add('text')
            ->add('email', EmailType::class)
            ->add('photo', FileType::class, [
                'required' => false,
                'label' => 'Attach a photo (optional) *This file should be less than 1MB and a JPEG or PNG format.*',
                'mapped' => false,
                'constraints' => [
                    new Image(['maxSize' => '1024k'])
                ]

            ])

            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
