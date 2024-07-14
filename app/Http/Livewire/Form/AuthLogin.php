<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\WebsiteBase\app\Models\User as WebsiteUser;

class AuthLogin extends ModelBase
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
     * @var array|string[]
     */
    public array $formActionButtons = [
        'password-forget' => 'form::components.form.actions.links.password-forget',
        'register-new'    => 'form::components.form.actions.links.register-new',
        'Login'           => 'form::components.form.actions.login',
    ];

    /**
     * Overwrite this to set up the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('login');
    }

    /**
     * @param  mixed  $livewireId
     * @return RedirectResponse|void
     */
    #[On('login')]
    public function login(mixed $livewireId)
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        if ($this->authenticate()) {
            session()->regenerate();
            return redirect()->intended();
        } else {
            // Open this form again (with errors)!
            $this->reopenFormIfNeeded();
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     * @return bool
     */
    public function authenticate(): bool
    {
        if (!$this->ensureIsNotRateLimited()) {
            $this->addErrorMessage(__('auth.failed2'));
            Log::error(sprintf("User Login failed. Rate Limiter."), [__METHOD__]);
            return false;
        }

        $credentials = [
            'email'    => data_get($this->formObjectAsArray, 'email'),
            'password' => data_get($this->formObjectAsArray, 'password'),
        ];

        // Check user exist, is disabled, deleted or want to be deleted
        /** @var WebsiteUser $u */
        $u = app(WebsiteUser::class)->with([])->where('email', $credentials['email'])->first();
        if (!$u || !$u->canLogin()) {
            RateLimiter::hit($this->throttleKey());
            $this->addErrorMessage(__('auth.failed'));
            Log::error(sprintf("User Login failed. User disabled or deleted '%s'. Name: %s. Can login: %s.",
                $credentials['email'], $u->name ?? '-', $u && $u->canLogin()), [__METHOD__]);

            return false;
        }

        // Attempt to authenticate a user using the given credentials.
        if (!Auth::attempt($credentials, true)) {
            RateLimiter::hit($this->throttleKey());

            $this->addErrorMessage(__('auth.failed'));

            Log::error(sprintf("User Login failed. Attempted to authenticate '%s'. Name: %s. Can login: %s.",
                $credentials['email'], $u->name ?? '-', $u && $u->canLogin()), [__METHOD__]);
            return false;
        }

        RateLimiter::clear($this->throttleKey());
        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     * @return bool
     */
    public function ensureIsNotRateLimited(): bool
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return true;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        //        throw ValidationException::withMessages([
        //            'email' => trans('auth.throttle', [
        //                'seconds' => $seconds,
        //                'minutes' => ceil($seconds / 60),
        //            ]),
        //        ]);
        return false;
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::transliterate(Str::lower(data_get($this->formObjectAsArray, 'email',
                '')).'|'.Request::getClientIp());
    }

}
