<?php
namespace App\Tests\UnitAndIntegration\EventSubscriber\DoctrineEventSubscriber;

use App\Entity\Picture;
use App\Entity\Product;
use App\EventSubscriber\DoctrineEventSubscriber\PictureRemoveSubscriber;
use App\Service\PicturePathResolver;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group EventSubscriber
 */
class PictureRemoveSubscriberTest extends KernelTestCase
{
    private EventManager $eventManager;

    private EntityManagerInterface $em;

    private CacheManager|MockObject $imagineCacheManager;

    private PicturePathResolver|MockObject $picturePathResolver;

    private MockObject|PictureRemoveSubscriber $pictureRemoveSubscriberMock;

    private PictureRemoveSubscriber $pictureRemoveSubscriber;


    public function setUp(): void 
    {
        parent::setUp();

        self::bootKernel();

        $this->pictureRemoveSubscriberMock = $this->createMock(PictureRemoveSubscriber::class);

        $this->eventManager = new EventManager;

        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->imagineCacheManager = $this->createMock(CacheManager::class);
        $this->picturePathResolver = $this->createMock(PicturePathResolver::class);

        $this->pictureRemoveSubscriber = new PictureRemoveSubscriber($this->picturePathResolver, $this->imagineCacheManager);
    }

    public function testPostRemoveIsCalledWhenEntityIsRemoved()
    {
        $this->pictureRemoveSubscriberMock->expects($this->once())
                                        ->method('getSubscribedEvents')
                                        ->willReturn($this->pictureRemoveSubscriber->getSubscribedEvents())
                                        ;

        $this->eventManager->addEventSubscriber($this->pictureRemoveSubscriberMock);

        $this->pictureRemoveSubscriberMock->expects($this->once())
                                ->method('postRemove')
                                ;
        $this->eventManager->dispatchEvent(
            Events::postRemove, 
            new PostRemoveEventArgs(new Picture, $this->em)
        );
    }
    
    public function testPostRemoveDontWorksWhenEntityIsNotPicture()
    {
        $this->picturePathResolver->expects($this->never())
                                    ->method('getPath')
                                    ;
        $this->imagineCacheManager->expects($this->never())
                                    ->method('remove')
                                    ;
        $this->pictureRemoveSubscriber->postRemove(new LifecycleEventArgs(new Product, $this->em));
    }

    public function testPostRemoveCorrectResolvePath()
    {
        $picture = new Picture;

        $this->picturePathResolver->expects($this->once())
                                    ->method('getPath')
                                    ->with($picture)
                                    ;
        $this->pictureRemoveSubscriber->postRemove(new LifecycleEventArgs($picture, $this->em));
    }
    
    public function testPostRemoveCorrectRemove()
    {
        $this->picturePathResolver->expects($this->once())
                                    ->method('getPath')
                                    ->willReturn('resolved_path')
                                    ;
        $this->imagineCacheManager->expects($this->once())
                                    ->method('remove')
                                    ->with('resolved_path')
                                    ;
        $this->pictureRemoveSubscriber->postRemove(new LifecycleEventArgs(new Picture, $this->em));
    }

}