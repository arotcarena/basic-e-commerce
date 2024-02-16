<?php

namespace App\Form\Admin;

use App\Config\SiteConfig;
use Symfony\Component\Form\AbstractType;
use App\Form\Admin\DataModel\ReviewFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReviewFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rate', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5
                ]
                ])
            ->add('moderationStatus', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    SiteConfig::MODERATION_STATUS_PENDING_LABEL => SiteConfig::MODERATION_STATUS_PENDING,
                    SiteConfig::MODERATION_STATUS_ACCEPTED_LABEL => SiteConfig::MODERATION_STATUS_ACCEPTED,
                    SiteConfig::MODERATION_STATUS_REFUSED_LABEL => SiteConfig::MODERATION_STATUS_REFUSED
                ]
            ])
            ->add('sortBy', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Meilleures notes d\'abord' => 'rate_DESC',
                    'Plus mauvaises notes d\'abord' => 'rate_ASC',
                    'Plus récents d\'abord' => 'createdAt_DESC',
                    'Plus anciens d\'abord' => 'createdAt_ASC' 
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReviewFilter::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
