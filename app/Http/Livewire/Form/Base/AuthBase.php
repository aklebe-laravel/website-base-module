<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form\Base;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuthBase extends ModelBaseExtraAttributes
{
    /**
     * This form is opened by default.
     *
     * @var bool
     */
    public bool $isFormOpen = true;

    /**
     * Decides form can send by key ENTER
     *
     * @var bool
     */
    public bool $canKeyEnterSendForm = true;

    /**
     * Ensure the login request is not rate limited.
     *
     * @return bool
     */
    public function ensureIsNotRateLimited(): bool
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 7)) {
            return true;
        }

        return false;
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower(data_get($this->dataTransfer,
                'email',
                '')).'|'.Request::getClientIp());
    }

}
