<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

class AuthRegister extends ModelBase
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
        'already-registered' => 'form::components.form.actions.links.already-registered',
        'register'           => 'form::components.form.actions.register',
    ];

    /**
     * Overwrite this to set up the default Call if Enter pressed in Form
     *
     * @return string
     */
    protected function getDefaultWireFormAccept(): string
    {
        return $this->getWireCallString('register');
    }

    /**
     * @param  mixed  $livewireId
     * @return RedirectResponse|void
     */
    #[On('register')]
    public function register(mixed $livewireId)
    {
        if (!$this->checkLivewireId($livewireId)) {
            return;
        }

        $res = $this->saveFormData();
        if (!$res->hasErrors()) {

            if ($userId = data_get($res->responseData, 'data.created.0')) {

                $user = app(\App\Models\User::class)->with([])->find($userId);

                // @todo: Create new Registered listener to send email

                // @todo: Email to SiteOwner or stuff?

                event(new Registered($user));

                Auth::login($user);

                return redirect()->intended();

            } else {
                // fail
            }

        } else {
            $this->addErrorMessages($res->getErrors());
            // Open this form again (with errors)!
            $this->reopenFormIfNeeded();
        }

    }


}
