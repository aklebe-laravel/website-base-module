<?php
declare(strict_types = 1);

namespace Modules\WebsiteBase\tests\Unit;

use Modules\SystemBase\tests\Unit\Isolated\IsolatedTestCase;
use Modules\WebsiteBase\app\Services\MediaService;

/**
 * MediaService helpers which work without the application (GIF detection, box size without upscaling).
 */
class MediaServiceTest extends IsolatedTestCase
{
    /**
     * Animated GIF 4x3 with 2 frames (red, blue).
     */
    const string ANIMATED_GIF = 'R0lGODlhBAADAPAAAP8AAAAA/yH/C05FVFNDQVBFMi4wAwEAAAAh+QQEFAAAACH/C0ltYWdlTWFnaWNrDmdhbW1hPTAuNDU0NTQ1ACwAAAAABAADAAACA4SPVgAh+QQEFAAAACH/C0ltYWdlTWFnaWNrDmdhbW1hPTAuNDU0NTQ1ACwAAAAABAADAAACA4yPVgA7';

    /**
     * Still GIF 4x3 (red).
     */
    const string STILL_GIF = 'R0lGODlhBAADAPAAAP8AAAAAACH5BAAAAAAAIf8LSW1hZ2VNYWdpY2sOZ2FtbWE9MC40NTQ1NDUALAAAAAAEAAMAAAIDhI9WADs=';

    /**
     * @var MediaService
     */
    protected MediaService $service;

    /**
     * @var string
     */
    protected string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MediaService();
        $this->dir = $this->makeTempDir();
    }

    public function test_gif_frames(): void
    {
        $animated = $this->makeFile($this->dir.'/animated.gif', base64_decode(self::ANIMATED_GIF));
        $still = $this->makeFile($this->dir.'/still.gif', base64_decode(self::STILL_GIF));
        $noGif = $this->makeFile($this->dir.'/no.jpg', "\xFF\xD8\xFF\xE0 not a gif");

        $this->assertSame(2, $this->service->countGifFrames($animated));
        $this->assertSame(1, $this->service->countGifFrames($still));
        $this->assertSame(0, $this->service->countGifFrames($noGif));
        $this->assertSame(0, $this->service->countGifFrames($this->dir.'/missing.gif'));

        $this->assertTrue($this->service->isAnimatedGif($animated));
        $this->assertFalse($this->service->isAnimatedGif($still));
        $this->assertFalse($this->service->isAnimatedGif($noGif));

        $this->assertTrue($this->service->canProcessAnimatedGif($animated));
    }

    public function test_box_without_upscaling(): void
    {
        // bigger image: box unchanged (contain() scales down)
        $this->assertSame([1000, 1000], $this->service->getBoxWithoutUpscaling(2000, 1000, 1000, 1000));
        $this->assertSame([1000, 500], $this->service->getBoxWithoutUpscaling(300, 600, 1000, 500));
        // same size
        $this->assertSame([1000, 1000], $this->service->getBoxWithoutUpscaling(1000, 1000, 1000, 1000));
        // smaller image: the box shrinks, the image keeps its size
        $this->assertSame([480, 480], $this->service->getBoxWithoutUpscaling(480, 270, 1000, 1000));
        $this->assertSame([100, 50], $this->service->getBoxWithoutUpscaling(100, 20, 200, 100));
        // invalid image size: box unchanged
        $this->assertSame([1000, 1000], $this->service->getBoxWithoutUpscaling(0, 0, 1000, 1000));
    }
}
