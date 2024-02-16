<?php

namespace App\Twig\Runtime;

use App\Entity\Picture;
use App\Service\PicturePathResolver;
use Twig\Extension\RuntimeExtensionInterface;

class PicturePathResolverExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private PicturePathResolver $picturePathResolver
    )
    {
        // Inject dependencies if needed
    }

    public function getPath(?Picture $picture, string $filter = null)
    {
        return $this->picturePathResolver->getPath($picture, $filter);
    }
    public function getAlt(?Picture $picture)
    {
        return $this->picturePathResolver->getAlt($picture);
    }
}
