<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class EstablishmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Restaurant' => 'Restaurant',
                    'Izakaya' => 'Izakaya',
                ],
            ])
            ->add('description', TextareaType::class)
            ->add('address', TextType::class, [
                'label' => 'Adresse'
            ])
            ->add('price', NumberType::class, [
                'required' => false,
                'label' => 'Prix moyen',
                'scale' => 1,
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site web',
                'required' => false,
            ])
            ->add('phone', IntegerType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('rating', NumberType::class, [
                'label' => 'Rating',
                'required' => false,
                'scale' => 1,
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                    'step' => 0.1
                ]
            ])
            ->add('slug', TextType::class, [
                'label' => 'slug',
                'required' => false,
            ])
            ->add('picture', UrlType::class, [
                'label' => 'Photo',
                'required' => false,                
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'required' => false,
                'choices' =>[
                    'Proposé' => 0,
                    'Activé' => 1,
                    'Désactivé' => 2,
                ]
            ])
            ->add('openingtime', TextareaType::class, [
                'label' => 'Horaires d\'ouverture',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Establishment',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
