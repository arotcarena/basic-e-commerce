<?php
namespace App\Tests\Utils;

use Doctrine\ORM\Repository\RepositoryFactory;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

trait FixturesTrait 
{
    public function getDatabaseTool(?KernelBrowser $client = null): AbstractDatabaseTool
    {
        /** @var ContainerInterface */
        $container = $client ? $client->getContainer(): static::getContainer();
        return $container->get(DatabaseToolCollection::class)->get();
    }
    public function loadFixtures(array $fixturesFiles, ?KernelBrowser $client = null)
    {
        $this->getDatabaseTool($client)->loadFixtures($fixturesFiles);
    }

    public function getFaker(): Generator
    {
        return Factory::create();
    }

    public function findEntity(string $repositoryClass, array $criteria = [], ?KernelBrowser $client = null)
    {
        /** @var ContainerInterface */
        $container = $client ? $client->getContainer(): static::getContainer();
        $repository = $container->get($repositoryClass);
        return $repository->findOneBy($criteria);
    }
}