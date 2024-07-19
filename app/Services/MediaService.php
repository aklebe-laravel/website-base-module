<?php

namespace Modules\WebsiteBase\app\Services;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic as ImageStatic;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Models\MediaItem;

class MediaService extends BaseService
{
    /**
     * Defined thumbs (inclusive original) sorted from big to small
     */
    const availableThumbs = [
        // '' original (no thumb)
        ''              => [
            'config' => [
                'width'           => 'catalog.product.image.width',
                'width_default'   => 1000,
                'height'          => 'catalog.product.image.height',
                'height_default'  => 1000,
                'quality'         => 'catalog.product.image.quality',
                'quality_default' => 90,
            ],
        ],
        // medium thumb
        'thumbs_medium' => [
            'config' => [
                'width'           => 'catalog.product.image_thumb_medium.width',
                'width_default'   => 200,
                'height'          => 'catalog.product.image_thumb_medium.height',
                'height_default'  => 200,
                'quality'         => 'catalog.product.image_thumb_medium.quality',
                'quality_default' => 90,
            ],
        ],
        // small thumb
        'thumbs_small'  => [
            'config' => [
                'width'           => 'catalog.product.image_thumb_small.width',
                'width_default'   => 50,
                'height'          => 'catalog.product.image_thumb_small.height',
                'height_default'  => 50,
                'quality'         => 'catalog.product.image_thumb_small.quality',
                'quality_default' => 90,
            ],
        ],
    ];

    /**
     * @param  MediaItem  $mediaModel
     * @param  string     $tmpFile
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createMediaFile(MediaItem $mediaModel, string $tmpFile): void
    {
        $config = app('website_base_config');

        $aspectRatio = function (Constraint $constraint) {
            $constraint->aspectRatio();
        };

        $img = ImageStatic::make($tmpFile);
        $loopIndex = 0;
        foreach (self::availableThumbs as $thumbName => $data) {
            $configKeyWidth = data_get($data, 'config.width');
            $configKeyWidthDefault = (int) data_get($data, 'config.width_default');
            $configKeyHeight = data_get($data, 'config.height');
            $configKeyHeightDefault = (int) data_get($data, 'config.height_default');
            $configKeyQuality = data_get($data, 'config.quality');
            $configKeyQualityDefault = (int) data_get($data, 'config.quality_default');

            $width = (int) $config->get($configKeyWidth, $configKeyWidthDefault);
            $height = (int) $config->get($configKeyHeight, $configKeyHeightDefault);
            $quality = (int) $config->get($configKeyQuality, $configKeyQualityDefault);

            // resize with aspect ratio
            $img->resize($width, $height, $aspectRatio);

            // canvas only once
            if ($loopIndex === 0) {
                $img->resizeCanvas($width, $height, 'center', false,
                    'f8f8f8'); // fit to $width and $height using background color
            }

            $this->saveToMedia($img, $mediaModel, $quality, $thumbName, ($loopIndex === 0));
            $loopIndex++;
        }
    }

    /**
     * Delete media files by model.
     * Only the files by $thumbPath will be deleted.
     *
     * @param  MediaItem  $mediaModel
     * @param  string     $thumbPath
     *
     * @return bool
     */
    public function deleteMediaFiles(MediaItem $mediaModel, string $thumbPath = ''): bool
    {
        $fileNamePrefix = sprintf($mediaModel->fileNamePrefixFormat, $mediaModel->id);
        $relativePath = $mediaModel->relative_path ?? '';
        $path = storage_path(app('system_base_file')->getValidPath($mediaModel->mediaPath.'/'.$thumbPath.'/'.$relativePath));
        $files = glob($path.'/'.$fileNamePrefix.'*');
        foreach ($files as $file) {
            if (is_file($file)) {
                if (!@unlink($file)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  MediaItem  $mediaModel
     * @return void
     */
    public function deleteAllMediaFiles(MediaItem $mediaModel): void
    {
        foreach (self::availableThumbs as $thumbName => $data) {
            $this->deleteMediaFiles($mediaModel, $thumbName);
        }
    }

    /**
     * @param  MediaItem  $mediaModel
     * @return void
     */
    public function deleteMediaItem(MediaItem $mediaModel): void
    {
        $this->deleteAllMediaFiles($mediaModel);
        $mediaModel->delete();
    }

    /**
     * @param  Image      $img
     * @param  MediaItem  $mediaModel
     * @param  int        $quality
     * @param  string     $thumbPath
     * @param  bool       $generate  if true generate a new filename and DB entry
     *
     * @return bool
     */
    public function saveToMedia(Image $img, MediaItem $mediaModel, int $quality = 90, string $thumbPath = '',
        bool $generate = false): bool
    {
        $relativePath = $mediaModel->relative_path ?? '';
        $fileNamePrefix = sprintf($mediaModel->fileNamePrefixFormat, $mediaModel->id);

        // delete previous file(s)
        if (!$this->deleteMediaFiles($mediaModel, $thumbPath)) {
            $this->error('Unable to delete files', [$mediaModel->id, $thumbPath, __METHOD__]);

            return false;
        }

        // calculate destination directory
        $path = storage_path(app('system_base_file')->getValidPath($mediaModel->mediaPath.'/'.$thumbPath.'/'.$relativePath));

        // create directory if not exists
        if (!is_dir($path)) {
            app('system_base_file')->createDir($path);
        }

        // the unique part is used to prevent browser caching after the image was changed
        $fileName = $mediaModel->file_name;
        if ($generate) {
            $uniqueId = uniqid(); // or just random_bytes()
            $fileName = $fileNamePrefix.$uniqueId.'.jpg';
        }

        $path = app('system_base_file')->getValidPath($path.'/'.$fileName);
        $img->save($path, $quality);

        if ($generate) {
            $mediaModel->update([
                'file_name'     => $fileName,
                'relative_path' => $relativePath,
            ]);
        }

        return true;
    }

}
