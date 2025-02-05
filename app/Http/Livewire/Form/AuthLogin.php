<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Modules\WebsiteBase\app\Http\Livewire\Form\Base\AuthBase;
use Modules\WebsiteBase\app\Models\User as WebsiteUser;

class AuthLogin extends AuthBase
{
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
     *
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
     *
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
            'email'    => data_get($this->dataTransfer, 'email'),
            'password' => data_get($this->dataTransfer, 'password'),
        ];

        // Check user exist, is disabled, deleted or want to be deleted
        /** @var WebsiteUser $user */
        $user = app(WebsiteUser::class)->with([])->where('email', $credentials['email'])->orWhere('name', $credentials['email'])->first();
        if (!$user || !$user->canLogin()) {
            RateLimiter::hit($this->throttleKey());
            $this->addErrorMessage(__('auth.failed'));
            Log::error(sprintf("User Login failed. User disabled or deleted '%s'. Name: %s. Can login: %s.",
                $credentials['email'],
                $user->name ?? '-',
                $user && $user->canLogin()),
                [__METHOD__]);

            return false;
        }

        // name used instead of email?
        if (app('system_base')->strCaseCompare($credentials['email'], $user->name)) {
            $credentials['email'] = $user->email;
        }

        // Attempt to authenticate a user using the given credentials.
        if (!Auth::attempt($credentials, true)) {
            RateLimiter::hit($this->throttleKey());
            $this->addErrorMessage(__('auth.failed'));
            Log::error(sprintf("User Login failed. Attempted to authenticate '%s'. Name: %s. Can login: %s.",
                $credentials['email'],
                $user->name ?? '-',
                $user && $user->canLogin()),
                [__METHOD__]);

            return false;
        }

        RateLimiter::clear($this->throttleKey());

        return true;
    }

}
