<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\Product;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('invoice_number')
            ->add('issue_date', null, [
                'widget' => 'single_text',
            ])
            ->add('due_date', null, [
                'widget' => 'single_text',
            ])
            ->add('amount')
            ->add('status')
            ->add('project_id', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name', // Assurez-vous que l'entité Project a un attribut 'name'
            ])
            ->add('products', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name', // Assurez-vous que l'entité Product a un attribut 'name'
                'multiple' => true,
                'expanded' => true, // Optionnel : utilisez des cases à cocher au lieu d'une liste déroulante
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
