<?php
namespace App\Service;

use App\Entity\Picture;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

use function PHPUnit\Framework\fileExists;

class PicturePathResolver 
{
    public function __construct(
        private UploaderHelper $helper,
        private CacheManager $imagineCacheManager
    )
    {

    }
    public function getPath(?Picture $picture, string $filter = null)
    {
        $path = '/img/default.jpg';
        if($picture)
        {
            if($resolvedPath = $this->helper->asset($picture, 'file'))
            {
                $path = $resolvedPath;
            }
        }
        if($filter)
        {
            return $this->imagineCacheManager->getBrowserPath($path, $filter);
        }
        return $path;
    }
    public function getAlt(?Picture $picture)
    {
        if($picture && $picture->getAlt())
        {
            return $picture->getAlt();
        }
        return 'Photo';
    }
}