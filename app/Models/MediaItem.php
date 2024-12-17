<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Storage;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\app\Models\MediaItem as MediaItemModel;
use Modules\WebsiteBase\database\factories\MediaItemFactory;

/**
 * @mixin IdeHelperMediaItem
 */
class MediaItem extends Model implements Attachable
{
    use HasFactory;
    use TraitBaseModel;

    const string MEDIA_TYPE_IMAGE = 'IMAGE';
    const string MEDIA_TYPE_ARCHIVE = 'ARCHIVE';
    const string MEDIA_TYPE_IMPORT = 'IMPORT';
    const string OBJECT_TYPE_DOWNLOAD = 'DOWNLOAD';
    const string OBJECT_TYPE_PRODUCT_IMAGE = 'PRODUCT_IMAGE';
    const string OBJECT_TYPE_CATEGORY_IMAGE = 'CATEGORY_IMAGE';
    const string OBJECT_TYPE_USER_AVATAR = 'USER_AVATAR';
    const string OBJECT_TYPE_IMPORT_PRODUCT = 'IMPORT_PRODUCT';
    const string OBJECT_TYPE_IMPORT_USER = 'IMPORT_USER';
    const string OBJECT_TYPE_IMPORT_CATEGORY = 'IMPORT_CATEGORY';

    /**
     * @var string
     */
    public const string fileNamePrefixFormat = "%s-";

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     *
     */
    const array MEDIA_TYPES = [
        self::MEDIA_TYPE_IMAGE   => [
            'extensions'  => ['gif', 'jpg', 'jpeg', 'png'],
            'description' => 'Images',
            'media_path'  => 'app/public/media',
            'objects'     => [
                self::OBJECT_TYPE_PRODUCT_IMAGE,
                self::OBJECT_TYPE_CATEGORY_IMAGE,
                self::OBJECT_TYPE_USER_AVATAR,
            ],
        ],
        self::MEDIA_TYPE_ARCHIVE => [
            'extensions'  => ['zip', 'rar'],
            'description' => 'Archives',
            'media_path'  => 'app/archive',
            'objects'     => [
                self::OBJECT_TYPE_DOWNLOAD,
            ],
        ],
        self::MEDIA_TYPE_IMPORT  => [
            'extensions'  => ['csv', 'json'],
            'description' => 'Imports',
            'media_path'  => 'app/import/users',
            'objects'     => [
                self::OBJECT_TYPE_IMPORT_PRODUCT,
            ],
        ],
    ];

    /**
     *
     */
    const array OBJECT_TYPES = [
        self::OBJECT_TYPE_DOWNLOAD       => [
            'description' => 'Download',
        ],
        self::OBJECT_TYPE_PRODUCT_IMAGE  => [
            'description' => 'Product Images',
        ],
        self::OBJECT_TYPE_CATEGORY_IMAGE => [
            'description' => 'Category Image',
        ],
        self::OBJECT_TYPE_USER_AVATAR    => [
            'description' => 'Avatar Image',
        ],
        self::OBJECT_TYPE_IMPORT_PRODUCT => [
            'description' => 'Product Imports',
        ],
    ];

    /**
     * You can use this instead of newFactory()
     *
     * @var string
     */
    public static string $factory = MediaItemFactory::class;

    /**
     * Multiple bootable model traits is not working
     * https://github.com/laravel/framework/issues/40645
     *
     * parent::construct() will not (or too early) be called without this construct()
     * so all trait boots also were not called.
     *
     * Important for \Modules\Acl\Models\Base\TraitBaseModel::bootTraitBaseModel
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->appends += [
            'final_path',
            'final_url',
            'final_thumb_medium_url',
            'final_thumb_small_url',
        ];

    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo($this::$userClassName);
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany($this::$userClassName)->withTimestamps();
    }

    /**
     * @return string[]
     */
    public static function getMediaTypesAsSelectOptions(): array
    {
        return array_map(function ($v) {
            return $v['description'];
        }, self::MEDIA_TYPES);
    }

    /**
     * @return array
     */
    public static function getObjectTypesAsSelectOptions(): array
    {
        return array_map(function ($v) {
            return $v['description'];
        }, self::OBJECT_TYPES);
    }

    /**
     * Get the relative path like:
     * - media/1-63e22ef18ca54.jpg
     * - media/thumbs_medium/1-63e22ef18ca54.jpg
     * - media/thumbs_small/my_relative_path/1-63e22ef18ca54.jpg
     *
     * @param  string  $thumbsSubFolder
     *
     * @return string
     */
    public function getRelativeFilePath(string $thumbsSubFolder = ''): string
    {
        return app('system_base_file')->getValidPath('media/'.$thumbsSubFolder.'/'.($this->relative_path ?? '').'/'.$this->file_name);
    }

    /**
     * @return string
     */
    public function getFinalPathAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return Storage::disk('public')->path($this->getRelativeFilePath());
    }

    /**
     * @return string
     */
    public function getFinalUrlAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath());
    }

    /**
     * @return string
     */
    public function getFinalThumbMediumUrlAttribute(): string
    {
        if (($this->media_type !== self::MEDIA_TYPE_IMAGE) || !$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath('thumbs_medium'));
    }

    /**
     * @return string
     */
    public function getFinalThumbSmallUrlAttribute(): string
    {
        if (($this->media_type !== self::MEDIA_TYPE_IMAGE) || !$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath('thumbs_small'));
    }

    /**
     * @return Attachment
     */
    public function toMailAttachment(): Attachment
    {
        return Attachment::fromPath($this->final_path);
    }

    /**
     * scope images()
     *
     * @param  Builder|self  $query
     *
     * @return Builder|self
     */
    public function scopeImages(Builder|self $query): Builder|self
    {
        return $query->where('media_type', '=', static::MEDIA_TYPE_IMAGE);
    }

    /**
     * scope scopeAvatars()
     *
     * @param  Builder|self  $query
     *
     * @return Builder|self
     */
    public function scopeUserAvatars(Builder|self $query): Builder|self
    {
        return $query->images()->where('object_type', '=', static::OBJECT_TYPE_USER_AVATAR);
    }

    /**
     * scope scopeProductImages()
     *
     * @param  Builder|self  $query
     *
     * @return Builder|self
     */
    public function scopeProductImages(Builder|self $query): Builder|self
    {
        return $query->images()->where('object_type', '=', static::OBJECT_TYPE_PRODUCT_IMAGE);
    }

    /**
     * scope scopeCategoryImages()
     *
     * @param  Builder|self  $query
     *
     * @return Builder|self
     */
    public function scopeCategoryImages(Builder|self $query): Builder|self
    {
        return $query->images()->where('object_type', '=', static::OBJECT_TYPE_CATEGORY_IMAGE);
    }

    /**
     * @param  Builder|self  $query
     *
     * @return Builder|self
     */
    public function scopeImports(Builder|self $query): Builder|self
    {
        return $query->where('media_type', '=', static::MEDIA_TYPE_IMPORT);
    }

    /**
     * @param  Builder|MediaItem  $query
     *
     * @return Builder|MediaItem
     */
    public function scopeProductImports(Builder|self $query): Builder|self
    {
        return $query->where('media_type', '=', static::MEDIA_TYPE_IMPORT)->where('object_type', '=', static::OBJECT_TYPE_IMPORT_PRODUCT);
    }

    /**
     * Get the extensions for $mediaType prepared for open file dialog
     *
     * @param  string  $mediaType
     *
     * @return string
     */
    public static function getMediaTypeExtensionsForHtml(string $mediaType): string
    {
        $result = '';
        $extensions = data_get(static::MEDIA_TYPES, $mediaType.'.extensions', []);
        foreach ($extensions as $extension) {
            if ($result) {
                $result .= ',';
            }
            $result .= '.'.$extension;
        }

        return $result;
    }
}
