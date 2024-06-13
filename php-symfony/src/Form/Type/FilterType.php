<?php

namespace App\Form\Type;

use App\Contracts\CreateProductRequest;
use App\Contracts\FilterRequest;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('category', TextType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,

                'choice_label' => 'name',

                'multiple' => false,
                'expanded' => false,
            ])
            ->add('minPrice', NumberType::class)
            ->add('maxPrice', NumberType::class)
            ->add('search', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FilterRequest::class,
            'category_choices' => [],
        ]);
    }
}