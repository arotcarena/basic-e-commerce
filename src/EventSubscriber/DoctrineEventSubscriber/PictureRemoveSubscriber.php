<?php
namespace App\EventSubscriber\DoctrineEventSubscriber;

use App\Entity\Picture;
use App\Service\PicturePathResolver;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class PictureRemoveSubscriber implements EventSubscriber
{
    public function __construct(
        private PicturePathResolver $picturePathResolver,
        private CacheManager $cacheManager 
    )
    {

    }


    public function getSubscribedEvents()
    {
        return [
            Events::postRemove
        ];
    }

    /**
     * Supprime les filtres liés à la picture
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        
        if ($entity instanceof Picture) {
            $path = $this->picturePathResolver->getPath($entity);
            $this->cacheManager->remove($path);
        }
    }
}

