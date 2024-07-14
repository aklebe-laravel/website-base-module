<?php

namespace Modules\WebsiteBase\tests\Feature\Auth;

use Livewire\Livewire;
use Modules\SystemBase\tests\TestCase;
use Modules\WebsiteBase\app\Http\Livewire\Form\AuthLogin;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $validUser = app(\Modules\WebsiteBase\app\Models\User::class)->frontendItems()->inRandomOrder()->where('email', 'like', '%js-dummy%@local.test')->first();
        $this->assertTrue(!!$validUser);

        $livewire = Livewire::test(AuthLogin::class);
        $livewire->set('formObjectAsArray.email', $validUser->email);
        $livewire->set('formObjectAsArray.password', '1234567');
        $livewire->call('login', $livewire->id());

        $this->assertAuthenticated();
        $livewire->assertRedirect();
    }
}
