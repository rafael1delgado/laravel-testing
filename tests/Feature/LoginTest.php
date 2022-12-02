<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A user can see the login view
     *
     * @return void
     */
    public function test_user_can_view_a_login()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * A user cannot enter invalid data in the login view
     *
     * @return void
     */
    public function test_user_cannot_autheticate_with_invalid_data()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'this-is-not-a-email',
            'password' => 'my-password'
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['email' => 'wrong-email']);
    }

    /**
     * A user can log in with an invalid password
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('my-password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * A user can authenticate with their credentials
     *
     * @return void
     */
    public function test_user_can_authenticate()
    {
        $user = User::factory()->create([
            'password' => bcrypt('my-password'),
        ])->first();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' =>  bcrypt('my-password'),
        ]);

        $this->actingAs($user, 'web')
            ->get('/dashboard')
            ->assertStatus(200);
    }
}
