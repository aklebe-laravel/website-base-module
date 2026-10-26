<?php

namespace Modules\WebsiteBase\tests\Feature\Auth;

use Livewire\Livewire;
use Modules\SystemBase\tests\TestCase;
use Modules\WebsiteBase\app\Http\Livewire\Form\AuthRegister;

class RegistrationTest extends TestCase
{
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $livewire = Livewire::test(AuthRegister::class);
        $name = 'New Feature Test User';
        $email = 'testing@local.test';
        $password = '1234567';
        $livewire->set('dataTransfer.name', $name)
            ->set('dataTransfer.email', $email)
            ->set('dataTransfer.password', $password)
            ->set('dataTransfer.__confirm__password', $password)
            ->set('dataTransfer.extra_attributes.user_register_hint', 'register test ...');
        $livewire->call('register', $livewire->id());

        $this->assertAuthenticated();
        $livewire->assertRedirect();
    }
}
