<?php

namespace Modules\WebsiteBase\tests\Feature\Auth;

use Livewire\Livewire;
use Modules\SystemBase\tests\TestCase;
use Modules\WebsiteBase\app\Http\Livewire\Form\AuthLogin;
use Modules\WebsiteBase\app\Models\User;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $validUser = app(User::class)->frontendItems()->inRandomOrder()->where('email', 'StuffTest1@local.test')->first();
        $this->assertTrue(!!$validUser);

        $livewire = Livewire::test(AuthLogin::class);
        $livewire->set('dataTransfer.email', $validUser->email);
        $livewire->set('dataTransfer.password', '1234567');
        $livewire->call('login', $livewire->id());

        $this->assertAuthenticated();
        $livewire->assertRedirect();
    }
}
