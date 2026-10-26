<?php
declare(strict_types=1);

namespace Modules\WebsiteBase\app\Http\Livewire\Form\Base;

use Livewire\Wireable;

class MediaState implements Wireable
{
    public function __construct(
        public array $uploadedFiles = []
    ) {}

    public function toLivewire(): array
    {
        return ['uploadedFiles' => $this->uploadedFiles];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['uploadedFiles']);
    }
}