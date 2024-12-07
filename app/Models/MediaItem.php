<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Storage;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
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

    /**
     * @var string
     */
    public string $mediaPath = 'app/public/media';

    /**
     * @var string
     */
    public string $fileNamePrefixFormat = "%s-";

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
        ],
        self::MEDIA_TYPE_ARCHIVE => [
            'extensions'  => ['zip', 'rar'],
            'description' => 'Archives',
        ],
    ];

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
        $result = [
            '' => __('No choice'),
        ];
        foreach (self::MEDIA_TYPES as $k => $v) {
            $result[$k] = $v['description'];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getObjectTypesAsSelectOptions(): array
    {
        $result = [
            '' => __('No choice'),
        ];
        foreach (self::OBJECT_TYPES as $k => $v) {
            $result[$k] = $v['description'];
        }

        return $result;
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
        if (!$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath('thumbs_medium'));
    }

    /**
     * @return string
     */
    public function getFinalThumbSmallUrlAttribute(): string
    {
        if (!$this->file_name) {
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

}
