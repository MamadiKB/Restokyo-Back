<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class FavorisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
/*         $builder
            ->add('idFavori', NumberType::class, [
                'required' => true,
            ])
        ; */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Establishment',
            'csrf_protection' => false,
        ]);
    }
}
