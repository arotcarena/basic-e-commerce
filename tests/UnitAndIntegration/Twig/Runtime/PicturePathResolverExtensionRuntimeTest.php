<?php
namespace App\Tests\UnitAndIntegration\Twig\Runtime;

use App\Entity\Picture;
use PHPUnit\Framework\TestCase;
use App\Service\PicturePathResolver;
use App\Twig\Runtime\PicturePathResolverExtensionRuntime;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group Twig
 */
class PicturePathResolverExtensionRuntimeTest extends TestCase
{
    public function testCorrectlyUsePicturePathResolverService()
    {
        $picturePathResolver = $this->createMock(PicturePathResolver::class);

        $picturePathResolverExtension = new PicturePathResolverExtensionRuntime($picturePathResolver);

        $picture = new Picture;

        /** @var MockObject $picturePathResolver */
        $picturePathResolver->expects($this->once())
                            ->method('getPath')
                            ->with($picture)
                            ->willReturn('retour')
                            ;
        
        $return = $picturePathResolverExtension->getPath($picture);
        $this->assertEquals('retour', $return);
    }
}