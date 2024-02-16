<?php

namespace App\Form\Admin;

use App\DataTransformer\PriceTransformer;
use App\Entity\Category;
use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use App\Form\Admin\DataModel\ProductFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'required' => false,
                'class' => Category::class,
                'choice_label' => 'name'
            ])
            ->add('subCategory', EntityType::class, [
                'required' => false,
                'class' => SubCategory::class,
                'choice_label' => 'name'
            ])
            ->add('minPrice', NumberType::class, [
                'required' => false
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false
            ])
            ->add('minStock', NumberType::class, [
                'required' => false
            ])
            ->add('maxStock', NumberType::class, [
                'required' => false
            ])
            ->add('q', TextType::class, [
                'required' => false
            ])
            ->add('sortBy', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'plus récents d\'abord' => 'createdAt_DESC',
                    'plus anciens d\'abord' => 'createdAt_ASC',
                    'du moins cher au plus cher' => 'price_ASC',
                    'du plus cher au moins cher' => 'price_DESC'
                ]
            ])
        ;

        $builder->get('minPrice')->addModelTransformer(new PriceTransformer);
        $builder->get('maxPrice')->addModelTransformer(new PriceTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'data_class' => ProductFilter::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
