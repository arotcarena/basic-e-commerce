<?php

namespace App\Form\Admin;

use App\Config\SiteConfig;
use Symfony\Component\Form\AbstractType;
use App\Form\Admin\DataModel\PurchaseFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    SiteConfig::STATUS_LABELS[SiteConfig::STATUS_PENDING] => SiteConfig::STATUS_PENDING,
                    SiteConfig::STATUS_LABELS[SiteConfig::STATUS_PAID] => SiteConfig::STATUS_PAID,
                    SiteConfig::STATUS_LABELS[SiteConfig::STATUS_SENT] => SiteConfig::STATUS_SENT,
                    SiteConfig::STATUS_LABELS[SiteConfig::STATUS_DELIVERED] => SiteConfig::STATUS_DELIVERED,
                    SiteConfig::STATUS_LABELS[SiteConfig::STATUS_CANCELED] => SiteConfig::STATUS_CANCELED
                ]
            ])
            ->add('sortBy', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Plus récentes d\'abord' => 'createdAt_DESC',
                    'Plus anciennes d\'abord' => 'createdAt_ASC',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseFilter::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
