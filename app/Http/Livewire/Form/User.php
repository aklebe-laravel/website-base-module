<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

class User extends ModelBase
{
    /**
     * @return void
     */
    protected function initLiveCommands(): void
    {
        $this->addViewModeCommand(self::viewModeExtended);
    }

    /**
     * @param  mixed  $livewireId
     * @param  mixed  $itemId
     *
     * @return bool
     * @throws \Exception
     */
    #[On('delete-item')]
    public function deleteItem(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var \Modules\WebsiteBase\app\Models\User $user */
        if ($user = app(\App\Models\User::class)->with([])->find($itemId)) {
            $result = $user->deleteIn3Steps();
            if ($result['success']) {
                $this->addSuccessMessage($result['message']);
            } else {
                $this->addErrorMessages($result['message']);
            }
        }

        $this->closeFormAndRefreshDatatable();

        return true;
    }
}
