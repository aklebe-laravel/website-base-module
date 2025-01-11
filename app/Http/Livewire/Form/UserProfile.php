<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use App\Models\User as AppUser;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Livewire\Attributes\On;
use Modules\WebsiteBase\app\Models\User as UserModel;

class UserProfile extends User
{
    /**
     * This form is opened by default.
     *
     * @var bool
     */
    public bool $isFormOpen = true;

    /**
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     *
     * @return bool
     * @throws Exception
     */
    #[On('delete-item')]
    public function deleteItem(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var UserModel $user */
        if ($user = app(AppUser::class)->with([])->find($itemId)) {
            $result = $user->deleteIn3Steps();
            if ($result['success']) {
                $this->addSuccessMessage(__("User was deleted."));
            } else {
                $this->addErrorMessages($result['message']);
            }
        }

        Redirect::route('login')->with('message', 'Operation Successful!'); // maybe message never shown this way
        return true;
    }


}
