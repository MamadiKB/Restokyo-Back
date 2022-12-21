<?php

namespace App\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email(),
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'mapped' => false,
            ])
            ->add('pseudo', TextType::class)
            ->add('picture', TextType::class, [ 
                'required' => false,
            ])
            ->add('lastname', TextType::class, [ 
                'required' => false,
            ])
            ->add('firstname', TextType::class, [ 
                'required' => false,
            ])           
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                // Form
                $form = $event->getForm();
                // Edit
                $form->add('password', PasswordType::class, [
                    // For the edit
                    'empty_data' => '',
                    'attr' => [
                        'placeholder' => 'Laissez vide si inchangé'
                    ],
                    'constraints' => [
                        new Regex("/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}|:<>,.?\/~-])(?=.{8,})/"),
                    ],
                    // This field will not automaticaly mapped on the entity
                    // The $user's propety $password will not modify by the form's processing
                    'mapped' => false,
                    'required' => false,
                ]);
            });
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'csrf_protection' => false,
        ]);
    }
}
