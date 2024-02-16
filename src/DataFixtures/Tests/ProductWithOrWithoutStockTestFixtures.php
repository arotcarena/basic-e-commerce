<?php
namespace App\DataFixtures\Tests;

use Faker\Factory;
use Faker\Generator;
use DateTimeImmutable;
use App\Entity\Product;
use Bezhanov\Faker\Provider\Commerce;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Tests\CategoryTestFixtures;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductWithOrWithoutStockTestFixtures extends Fixture implements DependentFixtureInterface
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
        $products = []; // pour suggestedProducts

        $categories = $this->categoryRepository->findAll();
        
        //stock
        $category = $this->faker->randomElement($categories);
        $subCategory = $this->faker->randomElement($category->getSubCategories()->toArray());
        $product = new Product;
        $product->setPublicRef(str_shuffle('aAzZeErRtT0123456789'))
                ->setPrivateRef(str_shuffle('aAzZeErRtT0123456789'))
                ->setDesignation('stock test')
                ->setSlug(
                    'stock-test-1'
                )
                ->setPrice(2000)
                ->setStock(0)
                ->setCategory($category)
                ->setSubCategory($subCategory)
                ->setCreatedAt(new DateTimeImmutable())
                ;
        $manager->persist($product);
        $products[] = $product;

        $category = $this->faker->randomElement($categories);
        $subCategory = $this->faker->randomElement($category->getSubCategories()->toArray());
        $product = new Product;
        $product->setPublicRef(str_shuffle('aAzZeErRtT0123456789'))
                ->setPrivateRef(str_shuffle('aAzZeErRtT0123456789'))
                ->setDesignation('stock test')
                ->setSlug(
                    'stock-test-2'
                )
                ->setPrice(2000)
                ->setStock(1)
                ->setCategory($category)
                ->setSubCategory($subCategory)
                ->setCreatedAt(new DateTimeImmutable())
                ;
        $manager->persist($product);
        $products[] = $product;

        foreach($products as $product)
        {
            $suggestedProduct = $this->faker->randomElement($products);
            if($product->getSlug() !== $suggestedProduct->getSlug())
            {
                $product->addSuggestedProduct($suggestedProduct);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategoryTestFixtures::class];
    }
}