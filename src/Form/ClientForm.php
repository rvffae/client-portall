<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Company;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name')
            ->add('last_name')
            ->add('email')
            ->add('phone')
            ->add('adress')
            ->add('city')
            ->add('state')
            ->add('zip_code')
            ->add('country')
            ->add('created_at')
            ->add('updated_at')
            ->add('company_id', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
