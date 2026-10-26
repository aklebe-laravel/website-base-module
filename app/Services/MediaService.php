<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Image;
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
                // Laravel 13 nativer Einstiegspunkt
                $img = Image::fromPath($originalMediaFile);
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

                    if ($loopIndex === 0) {
                        // KORREKTUR: "contain" ersetzt das alte resize + resizeCanvas.
                        // Es passt das Bild proportional ein und füllt den Rest mit der Farbe f8f8f8.
                        // Wichtig: Da Laravel immutable ist, fangen wir das Ergebnis ab!
                        $processedImage = $img->contain(width: $width, height: $height, background: 'f8f8f8');
                    } else {
                        // REFACTOR: scale() ersetzt resize() mit AspectRatio-Constraint automatisiert
                        $processedImage = $img->scale(width: $width, height: $height);
                    }

                    $this->saveImageToMedia($processedImage, $mediaModel, $originalMediaFile, $quality, $thumbName, ($loopIndex === 0));
                    $loopIndex++;
                }
                break;

            default:
                $this->saveImport($mediaModel, $originalMediaFile);
                break;
        }
    }

    /**
     * @param  \Illuminate\Image\Image  $img  // Korrekter Laravel 13 Image Type-Hint
     * @param  MediaItem                $mediaModel
     * @param  string                   $originalMediaFile
     * @param  int                      $quality
     * @param  string                   $thumbPath
     * @param  bool                     $generate
     *
     * @return bool
     */
    public function saveImageToMedia(\Illuminate\Image\Image $img, MediaItem $mediaModel, string $originalMediaFile, int $quality = 90, string $thumbPath = '', bool $generate = false): bool
    {
        $relativePath = $mediaModel->relative_path ?? '';
        $fileNamePrefix = sprintf(MediaItem::fileNamePrefixFormat, $mediaModel->id);

        if (!$this->deleteMediaFiles($mediaModel, $thumbPath)) {
            $this->error('Unable to delete files', [$mediaModel->id, $thumbPath, __METHOD__]);

            return false;
        }

        $path = $this->getMediaItemPath($mediaModel, $thumbPath, false);

        if (!is_dir($path)) {
            app('system_base_file')->createDir($path);
        }

        $fileName = $mediaModel->file_name;
        if ($generate) {
            $uniqueId = uniqid();
            $fileName = $fileNamePrefix.$uniqueId.'.jpg';
        }

        $path = app('system_base_file')->getValidPath($path.'/'.$fileName);

        // REFACTOR: Da $img keine lokale save()-Methode hat, extrahieren wir die manipulierten
        // JPEG-Bytes inklusive Qualität und schreiben sie klassisch in die Datei.
        $imageBytes = $img->toJpeg()->quality($quality)->toBytes();

        if (file_put_contents($path, $imageBytes) !== false) {

            if ($generate) {
                $mediaModel->update([
                    'file_name'     => $fileName,
                    'relative_path' => $relativePath,
                    'extern_url'    => $originalMediaFile,
                ]);
            }

            return true;
        }

        Log::error("Unable to save: $path");

        return false;
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

        if (!$this->deleteMediaFiles($mediaModel)) {
            $this->error('Unable to delete files', [$mediaModel->id, __METHOD__]);

            return false;
        }

        $path = $this->getMediaItemPath($mediaModel, inclusiveFilenameIfNotEmpty: false);

        if (!is_dir($path)) {
            app('system_base_file')->createDir($path);
        }

        $ext = pathinfo($originalMediaFile, PATHINFO_EXTENSION);
        $uniqueId = uniqid();
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
     * Delete media files by model.
     */
    public function deleteMediaFiles(MediaItem $mediaModel, string $thumbPath = ''): bool
    {
        return $this->deleteMediaFilesById($mediaModel->getKey(), $mediaModel->relative_path, $thumbPath);
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
     * calculate media item directory based on type
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
        return MediaItem::with([])->images()->where('extern_url', $externUrl)->where('user_id', $userId)->first();
    }
}
