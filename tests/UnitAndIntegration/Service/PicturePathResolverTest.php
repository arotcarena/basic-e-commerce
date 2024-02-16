<?php
namespace App\Tests\UnitAndIntegration\Service;

use App\Entity\Picture;
use PHPUnit\Framework\TestCase;
use App\Service\PicturePathResolver;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\MockObject\MockObject;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @group Service
 */
class PicturePathResolverTest extends TestCase
{
    private MockObject $uploaderHelper;

    private MockObject $imagineCacheManager;

    private PicturePathResolver $picturePathResolver;


    public function setUp(): void 
    {   
        $this->uploaderHelper = $this->createMock(UploaderHelper::class);

        $this->imagineCacheManager = $this->createMock(CacheManager::class);

        $this->picturePathResolver = new PicturePathResolver($this->uploaderHelper, $this->imagineCacheManager);
    }

    public function testGetPathWithValidPictureFile()
    {
        $picture = new Picture;
        $this->uploaderHelper->expects($this->once())
                            ->method('asset')
                            ->with($picture, 'file')
                            ->willReturn('correct_path')
                            ;
        $this->assertEquals('correct_path', $this->picturePathResolver->getPath($picture));
    }
    public function testGetPathWithInvalidPictureFile() 
    {
        $picture = new Picture;
        $this->uploaderHelper->expects($this->once())
                            ->method('asset')
                            ->with($picture, 'file')
                            ->willReturn(null)
                            ;
        $this->assertEquals('/img/default.jpg', $this->picturePathResolver->getPath($picture));
    }
    public function testGetPathWithNullParam()
    {
        $this->uploaderHelper->expects($this->never())
                            ->method('asset');
        $this->assertEquals('/img/default.jpg', $this->picturePathResolver->getPath(null));
    }
    public function testGetAltWithPictureHavingAltProperty()
    {
        $picture = (new Picture)->setAlt('test_alt');
        $this->assertEquals('test_alt', $this->picturePathResolver->getAlt($picture));
    }
    public function testGetAltWithPictureNotHavingAltProperty()
    {
        $picture = new Picture;
        $this->assertEquals('Photo', $this->picturePathResolver->getAlt($picture));
    }
    public function testGetAltWithNullParam()
    {
        $this->assertEquals('Photo', $this->picturePathResolver->getAlt(null));
    }
    public function testGetPathWithValidPictureFileAndFilter()
    {
        $picture = new Picture;
        $this->uploaderHelper->expects($this->once())
                            ->method('asset')
                            ->with($picture, 'file')
                            ->willReturn('correct_path')
                            ;
        $this->imagineCacheManager->expects($this->once())
                                    ->method('getBrowserPath')
                                    ->with('correct_path', 'filter')
                                    ->willReturn('filtered_path')
                                    ;

        $this->assertEquals('filtered_path', $this->picturePathResolver->getPath($picture, 'filter'));
    }
    public function testGetPathWithInvalidPictureFileAndFilter()
    {
        $picture = new Picture;
        $this->uploaderHelper->expects($this->once())
                            ->method('asset')
                            ->with($picture, 'file')
                            ->willReturn(null)
                            ;
        $this->imagineCacheManager->expects($this->once())
                                    ->method('getBrowserPath')
                                    ->with('/img/default.jpg', 'filter')
                                    ->willReturn('default_filtered_path')
                                    ;
        $this->assertEquals('default_filtered_path', $this->picturePathResolver->getPath($picture, 'filter'));
    }
    public function testGetPathWithNullParamAndFilter()
    {
        $this->uploaderHelper->expects($this->never())
                            ->method('asset');
        $this->imagineCacheManager->expects($this->once())
                                    ->method('getBrowserPath')
                                    ->with('/img/default.jpg', 'filter')
                                    ->willReturn('default_filtered_path')
                                    ;
        $this->assertEquals('default_filtered_path', $this->picturePathResolver->getPath(null, 'filter'));
    }
    public function testGetPathWithNoFilter()
    {
        $this->imagineCacheManager->expects($this->never())
                                    ->method('getBrowserPath')
                                    ;
        $this->picturePathResolver->getPath(null);
        $this->picturePathResolver->getPath(new Picture);
    }
}