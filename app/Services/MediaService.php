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
     * @param  MediaItem  $mediaModel
     * @param  string  $tmpFile
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

        // create the original
        $width = (int) $config->get('catalog.product.image.width', 1000);
        $height = (int) $config->get('catalog.product.image.height', 1000);
        $quality = (int) $config->get('catalog.product.image.quality', 90);

        $img = ImageStatic::make($tmpFile);
        $img->resize($width, $height, $aspectRatio) // resize with aspect ratio
        ->resizeCanvas($width, $height, 'center', false, 'f8f8f8'); // fit to $width and $height using background color
        $this->saveToMedia($img, $mediaModel, $quality, '', true);

        // create thumb medium
        $width = (int) $config->get('catalog.product.image_thumb_medium.width', 200);
        $height = (int) $config->get('catalog.product.image_thumb_medium.height', 200);
        $quality = (int) $config->get('catalog.product.image_thumb_medium.quality', 80);
        $img->resize($width, $height); // $aspectRatio not needed because the source did already
        $this->saveToMedia($img, $mediaModel, $quality, 'thumbs_medium');

        // create thumb small
        $width = (int) $config->get('catalog.product.image_thumb_small.width', 50);
        $height = (int) $config->get('catalog.product.image_thumb_small.height', 50);
        $quality = (int) $config->get('catalog.product.image_thumb_medium.quality', 80);
        $img->resize($width, $height); // $aspectRatio not needed because the source did already
        $this->saveToMedia($img, $mediaModel, $quality, 'thumbs_small');
    }

    /**
     * Delete media files by model.
     * Only the files by $thumbPath will be deleted.
     *
     * @param  MediaItem  $mediaModel
     * @param  string  $thumbPath
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
     * @param  Image  $img
     * @param  MediaItem  $mediaModel
     * @param  int  $quality
     * @param  string  $thumbPath
     * @param  bool  $generate  if true generate a new filename and DB entry
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
