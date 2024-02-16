<?php
namespace App\DataFixtures\Shop;

use App\Entity\Category;
use App\Entity\SubCategory;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;



class CategoryFixtures extends Fixture
{
    // public function __construct(
    //     private SluggerInterface $slugger
    // )
    // {

    // }


    public function load(ObjectManager $manager)
    {
        $category = (new Category)
                    ->setName('Bijoux Femme')
                    ->setSlug('bijoux-femme')
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Bracelets')
                        ->setSlug('bracelets')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(1)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Boucles d\'oreilles')
                        ->setSlug('boucles-d-oreilles')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(2)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Colliers')
                        ->setSlug('colliers')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(3)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Pendentifs')
                        ->setSlug('pendentifs')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(4)
                    )
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setListPosition(1);
                    ;
        $manager->persist($category);
        
        $category = (new Category)
                    ->setName('Bijoux Homme')
                    ->setSlug('bijoux-homme')
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Bracelets')
                        ->setSlug('bracelets')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(1)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Boucles d\'oreilles')
                        ->setSlug('boucles-d-oreilles')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(2)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Colliers')
                        ->setSlug('colliers')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(3)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Pendentifs')
                        ->setSlug('pendentifs')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(4)
                    )
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setListPosition(2);
                    ;
        $manager->persist($category);

        $category = (new Category)
                    ->setName('Bijoux Enfant')
                    ->setSlug('bijoux-enfant')
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Bracelets')
                        ->setSlug('bracelets')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(1)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Boucles d\'oreilles')
                        ->setSlug('boucles-d-oreilles')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(2)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Colliers')
                        ->setSlug('colliers')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(3)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Pendentifs')
                        ->setSlug('pendentifs')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(4)
                    )
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setListPosition(3);
                    ;
        $manager->persist($category);

        $category = (new Category)
                    ->setName('Décoration & Détente')
                    ->setSlug('decoration-et-detente')
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Décoration')
                        ->setSlug('decoration')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(1)
                    )
                    ->addSubCategory(
                        (new SubCategory)
                        ->setName('Détente')
                        ->setSlug('detente')
                        ->setCreatedAt(new DateTimeImmutable())
                        ->setListPosition(2)
                    )
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setListPosition(4);
                    ;
        $manager->persist($category);

        $manager->flush();
    }
}