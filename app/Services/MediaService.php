<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic as ImageStatic;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Models\MediaItem;

class MediaService extends BaseService
{
    /**
     * @var string
     */
    public string $mediaFileRegexPattern = '#(\d+?)\-([0-9a-zA-Z]+)\.([0-9a-zA-Z]+)$#';

    /**
     * Defined thumbs (inclusive original) sorted from big to small
     */
    const array availableThumbs = [
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
     * @param  string     $originalMediaFile
     *
     * @return void
     */
    public function createMediaFile(MediaItem $mediaModel, string $originalMediaFile): void
    {
        $config = app('website_base_config');

        switch ($mediaModel->media_type) {
            case MediaItem::MEDIA_TYPE_IMAGE:
                $aspectRatio = function (Constraint $constraint) {
                    $constraint->aspectRatio();
                };

                // @todo: check for image ...
                $img = ImageStatic::make($originalMediaFile);
                $loopIndex = 0;
                foreach (self::availableThumbs as $thumbName => $data) {
                    $configKeyWidth = data_get($data, 'config.width');
                    $configKeyWidthDefault = (int) data_get($data, 'config.width_default');
                    $configKeyHeight = data_get($data, 'config.height');
                    $configKeyHeightDefault = (int) data_get($data, 'config.height_default');
                    $configKeyQuality = data_get($data, 'config.quality');
                    $configKeyQualityDefault = (int) data_get($data, 'config.quality_default');

                    $width = (int) $config->getValue($configKeyWidth, $configKeyWidthDefault);
                    $height = (int) $config->getValue($configKeyHeight, $configKeyHeightDefault);
                    $quality = (int) $config->getValue($configKeyQuality, $configKeyQualityDefault);

                    // resize with aspect ratio
                    $img->resize($width, $height, $aspectRatio);

                    // canvas only once
                    if ($loopIndex === 0) {
                        $img->resizeCanvas($width, $height, 'center', false,
                            'f8f8f8'); // fit to $width and $height using background color
                    }

                    $this->saveImageToMedia($img, $mediaModel, $originalMediaFile, $quality, $thumbName, ($loopIndex === 0));
                    $loopIndex++;
                }
                break;

            //case MediaItem::MEDIA_TYPE_IMPORT:
            default:
                $this->saveImport($mediaModel, $originalMediaFile);
                break;
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
        return $this->deleteMediaFilesById($mediaModel->getKey(), $mediaModel->relative_path, $thumbPath);
    }

    /**
     * @param  string|int|null  $id
     * @param  string|null      $relativePath
     * @param  string|null      $thumbPath
     *
     * @return bool
     */
    public function deleteMediaFilesById(string|int|null $id = null, ?string $relativePath = null, ?string $thumbPath = null): bool
    {
        $fileNamePrefix = sprintf(MediaItem::fileNamePrefixFormat, $id);
        $relativePath = $relativePath ?? '';

        foreach (MediaItem::MEDIA_TYPES as $mediaType => $data) {
            $path = $this->getMediaItemPathRaw($mediaType, $relativePath, $thumbPath);
            $files = glob($path.'/'.$fileNamePrefix.'*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (!@unlink($file)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param  MediaItem  $mediaModel
     *
     * @return void
     */
    public function deleteAllMediaFilesByModel(MediaItem $mediaModel): void
    {
        foreach (self::availableThumbs as $thumbName => $data) {
            $this->deleteMediaFiles($mediaModel, $thumbName);
        }
    }

    /**
     * @param  MediaItem  $mediaModel
     *
     * @return void
     */
    public function deleteMediaItem(MediaItem $mediaModel): void
    {
        Log::debug("Deleting media item {$mediaModel->getKey()} and related files ...");
        $this->deleteAllMediaFilesByModel($mediaModel);
        $mediaModel->delete();
    }

    /**
     * calculate media item directory based on type
     *
     * @param  MediaItem  $mediaModel
     * @param  string     $thumbPath  useful for images only
     * @param  bool       $inclusiveFilenameIfNotEmpty
     *
     * @return string
     */
    public function getMediaItemPath(MediaItem $mediaModel, string $thumbPath = '', bool $inclusiveFilenameIfNotEmpty = true): string
    {
        $path = $this->getMediaItemPathRaw($mediaModel->media_type, $mediaModel->relative_path, $thumbPath);

        if ($inclusiveFilenameIfNotEmpty && $mediaModel->file_name) {
            $path .= '/'.$mediaModel->file_name;
        }

        return $path;
    }

    /**
     * @param  string|null  $mediaType
     * @param  string|null  $relativePath
     * @param  string|null  $thumbPath
     *
     * @return string
     */
    public function getMediaItemPathRaw(?string $mediaType = null, ?string $relativePath = null, ?string $thumbPath = null): string
    {
        $mediaSubPath = MediaItem::MEDIA_TYPES[$mediaType]['media_path'];
        $path = storage_path(app('system_base_file')->getValidPath($mediaSubPath));
        switch ($mediaType) {
            case MediaItem::MEDIA_TYPE_IMAGE:
                if ($thumbPath) {
                    $path .= '/'.$thumbPath;
                }
                break;
            default:
                break;
        }

        if ($relativePath) {
            $path .= '/'.$relativePath;
        }

        return $path;
    }

    /**
     * @param  Image      $img
     * @param  MediaItem  $mediaModel
     * @param  string     $originalMediaFile
     * @param  int        $quality
     * @param  string     $thumbPath
     * @param  bool       $generate  if true generate a new filename
     *
     * @return bool
     */
    public function saveImageToMedia(Image $img, MediaItem $mediaModel, string $originalMediaFile, int $quality = 90, string $thumbPath = '', bool $generate = false): bool
    {
        $relativePath = $mediaModel->relative_path ?? '';
        $fileNamePrefix = sprintf(MediaItem::fileNamePrefixFormat, $mediaModel->id);

        // delete previous file(s)
        if (!$this->deleteMediaFiles($mediaModel, $thumbPath)) {
            $this->error('Unable to delete files', [$mediaModel->id, $thumbPath, __METHOD__]);

            return false;
        }

        // calculate destination directory
        $path = $this->getMediaItemPath($mediaModel, $thumbPath, false);

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
                'extern_url'    => $originalMediaFile, // remember origin to avoid generate duplicates and waste disk space
            ]);
        }

        return true;
    }

    /**
     * @param  MediaItem  $mediaModel
     * @param  string     $originalMediaFile
     *
     * @return bool
     */
    public function saveImport(MediaItem $mediaModel, string $originalMediaFile): bool
    {
        $relativePath = $mediaModel->relative_path ?? '';
        $fileNamePrefix = sprintf(MediaItem::fileNamePrefixFormat, $mediaModel->id);

        // delete previous file(s)
        if (!$this->deleteMediaFiles($mediaModel)) {
            $this->error('Unable to delete files', [$mediaModel->id, __METHOD__]);

            return false;
        }

        // calculate destination directory
        $path = $this->getMediaItemPath($mediaModel, inclusiveFilenameIfNotEmpty: false);

        // create directory if not exists
        if (!is_dir($path)) {
            app('system_base_file')->createDir($path);
        }

        $ext = pathinfo($originalMediaFile, PATHINFO_EXTENSION);
        //$fileName = $mediaModel->file_name;
        $uniqueId = uniqid(); // or just random_bytes()
        $fileName = $fileNamePrefix.$uniqueId.'.'.$ext;

        $path = app('system_base_file')->getValidPath($path.'/'.$fileName);

        if (copy($originalMediaFile, $path)) {
            $mediaModel->update([
                'file_name'     => $fileName,
                'relative_path' => $relativePath,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Find all files in media path and compare it with MediaItems->file_name.
     * If no item was found, the file will be deleted.
     *
     * @param  string  $type
     * @param  bool    $simulate
     *
     * @return array
     */
    public function deleteUnusedMediaFiles(string $type, bool $simulate = false): array
    {
        $startTime = microtime(true);
        $fileService = app('system_base_file');
        $path = $this->getMediaItemPathRaw($type);

        $listByFilenames = [];
        $infoList = [
            'simulated'             => $simulate,
            'success'               => false,
            'type'                  => $type,
            'path'                  => $path,
            'files_total'           => 0,
            'files_matched_by_name' => 0,
            'files_deleted'         => 0,
            'process_in_seconds'    => 0,
        ];

        if (!is_dir($path)) {
            return $infoList;
        }
        $this->info("Searching files for type \"$type\" in \"$path\" ...");

        // find all files
        $fileService->runDirectoryFiles($path, function (string $file, array $sourcePathInfo) use (&$infoList, &$listByFilenames) {
            $infoList['files_total']++;
            $listByFilenames[$sourcePathInfo['basename']][] = $file;

            return $infoList;
        });

        $matchedIds = array_keys($listByFilenames);
        $mediaItemsExists = MediaItem::whereIn('file_name', $matchedIds)->pluck('file_name')->toArray();
        $diff = array_diff($matchedIds, $mediaItemsExists);
        $infoList['files_matched_by_name'] = count($diff);

        $deleted = 0;
        foreach ($diff as $id) {
            foreach ($listByFilenames[$id] as $file) {
                if ($simulate || unlink($file)) {
                    $deleted++;
                }
            }
        }

        $infoList['success'] = true;
        $infoList['files_deleted'] = $deleted;
        $infoList['process_in_seconds'] = number_format(microtime(true) - $startTime, 2, '.', '');

        $this->info(json_encode($infoList, JSON_PRETTY_PRINT));

        return $infoList;
    }

    /**
     * @param  int     $userId
     * @param  string  $externUrl
     *
     * @return MediaItem|null
     */
    public function findUserImageByOrigin(int $userId, string $externUrl): ?MediaItem
    {
        return MediaItem::with([])
            ->images()
            ->where('extern_url', $externUrl)
            ->where('user_id', $userId)
            ->first();
    }
}
