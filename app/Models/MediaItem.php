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
use Modules\WebsiteBase\Models\IdeHelperMediaItem;


/**
 * @mixin IdeHelperMediaItem
 */
class MediaItem extends Model implements Attachable
{
    use HasFactory;
    use TraitBaseModel;

    const MEDIA_TYPE_IMAGE = 'IMAGE';
    const MEDIA_TYPE_ARCHIVE = 'ARCHIVE';

    const OBJECT_TYPE_DOWNLOAD = 'DOWNLOAD';
    const OBJECT_TYPE_PRODUCT_IMAGE = 'PRODUCT_IMAGE';
    const OBJECT_TYPE_CATEGORY_IMAGE = 'CATEGORY_IMAGE';
    const OBJECT_TYPE_USER_AVATAR = 'USER_AVATAR';

    public string $mediaPath = 'app/public/media';

    public string $fileNamePrefixFormat = "%s-";

    protected $guarded = [];

    protected $appends = [
        'final_path',
        'final_url',
        'final_thumb_medium_url',
        'final_thumb_small_url',
    ];

    const MEDIA_TYPES = [
        self::MEDIA_TYPE_IMAGE   => [
            'extensions'  => ['gif', 'jpg', 'jpeg', 'png'],
            'description' => 'Images',
        ],
        self::MEDIA_TYPE_ARCHIVE => [
            'extensions'  => ['zip', 'rar'],
            'description' => 'Archives',
        ],
    ];

    const OBJECT_TYPES = [
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
     * Multiple bootable model traits is not working
     * https://github.com/laravel/framework/issues/40645
     *
     * parent::construct() will not (or too early) be called without this construct()
     * so all trait boots also were not called.
     *
     * Important for \Modules\Acl\Models\Base\TraitBaseModel::bootTraitBaseModel
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo($this::$userClassName);
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany($this::$userClassName)->withTimestamps();
    }

    /**
     * @return string[]
     */
    public static function getMediaTypesAsSelectOptions(): array
    {
        $result = [
            '' => '[Keine Auswahl]',
        ];
        foreach (self::MEDIA_TYPES as $k => $v) {
            $result[$k] = $v['description'];
        }

        return $result;
    }

    public static function getObjectTypesAsSelectOptions(): array
    {
        $result = [
            '' => '[Keine Auswahl]',
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

    public function getFinalPathAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return Storage::disk('public')->path($this->getRelativeFilePath());
    }

    public function getFinalUrlAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath());
    }

    public function getFinalThumbMediumUrlAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath('thumbs_medium'));
    }

    public function getFinalThumbSmallUrlAttribute(): string
    {
        if (!$this->file_name) {
            return '';
        }

        return Storage::url($this->getRelativeFilePath('thumbs_small'));
    }

    public function toMailAttachment()
    {
        return Attachment::fromPath($this->final_path);
    }

}
