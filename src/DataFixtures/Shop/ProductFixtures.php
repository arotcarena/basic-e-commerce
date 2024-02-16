<?php
namespace App\DataFixtures\Shop;

use Faker\Factory;
use Faker\Generator;
use DateTimeImmutable;
use App\Entity\Picture;
use App\Entity\Product;
use App\Entity\Category;
use Bezhanov\Faker\Provider\Commerce;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\Shop\CategoryFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;


    public function __construct(
        private SluggerInterface $slugger,
        private CategoryRepository $categoryRepository
    )
    {
        $this->faker = Factory::create();
        $this->faker->addProvider(new Commerce($this->faker));
    }
    
    public function load(ObjectManager $manager)
    {
        $categories = $this->categoryRepository->findAll();

        $products = []; // pour suggestedProducts
        for ($i=0; $i < 100; $i++) { 
            $designation = $this->faker->productName();
            /** @var Category */
            $category = $this->faker->randomElement($categories);
            $subCategory = $this->faker->randomElement($category->getSubCategories()->toArray());
            $product = new Product;
            $product->setPublicRef(str_shuffle('aAzZeErRtT0123456789'))
                    ->setPrivateRef(str_shuffle('aAzZeErRtT0123456789'))
                    ->setDesignation($designation)
                    ->setSlug(
                        strtolower($this->slugger->slug($designation))
                    )
                    ->setPrice(random_int(2000, 20000))
                    ->setStock(random_int(0, 10))
                    ->setCategory($category)
                    ->setSubCategory($subCategory)
                    ->setCreatedAt(new DateTimeImmutable())
                    ;
            $manager->persist($product);
            $products[] = $product;
        }

        foreach($products as $product)
        {
            $suggestedProduct = $this->faker->randomElement($products);
            if($product->getSlug() !== $suggestedProduct->getSlug())
            {
                $product->addSuggestedProduct($suggestedProduct);
            }
        }

        $product = new Product;
        $product->setPublicRef(str_shuffle('aAzZeErRtT0123456789'))
        ->setPrivateRef(str_shuffle('aAzZeErRtT0123456789'))
        ->setDesignation('produit sans sous-catégorie')
        ->setSlug(
            strtolower('produit-sans-ss-categorie')
        )
        ->setPrice(5000)
        ->setStock(1)
        ->setCategory($this->faker->randomElement($categories))
        ->setCreatedAt(new DateTimeImmutable())
        ->addSuggestedProduct($this->faker->randomElement($products))
        ;
        $manager->persist($product);

        $product = new Product;
        $product->setPublicRef(str_shuffle('aAzZeErRtT0123456789'))
        ->setPrivateRef(str_shuffle('aAzZeErRtT0123456789'))
        ->setDesignation('produit sans catégorie')
        ->setSlug(
            strtolower('produit-sans-categorie')
        )
        ->setPrice(random_int(2000, 20000))
        ->setStock(1)
        ->setCreatedAt(new DateTimeImmutable())
        ->addSuggestedProduct($this->faker->randomElement($products))
        ;
        $manager->persist($product);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategoryFixtures::class];
    }
}