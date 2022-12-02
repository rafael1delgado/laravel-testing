<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A user can see the register view
     *
     * @return void
     */
    public function test_user_can_view_a_register()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /**
     * A user cannot register with a short password
     *
     * @return void
     */
    public function test_user_cannot_register_with_short_password()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@contoso.com',
            'password' => '123',
            'confirm_password' => '123'
        ]);

        $response->assertSessionHasErrors('password')
            ->assertStatus(302);

        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@contoso.com'
        ]);
    }

    /**
     * A user cannot register when the password does not match the password with confirm password
     *
     * @return void
     */
    public function test_user_cannot_register_doesnot_match_password_with_confirm_password()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@contoso.com',
            'password' => 'my-password',
            'password_confirmation' => 'other-password'
        ]);

        $response->assertSessionHasErrors('password')
            ->assertStatus(302);

        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@contoso.com'
        ]);
    }

    /**
     * A user cannot register with an invalid email
     *
     * @return void
     */
    public function test_user_cannot_register_with_an_invalid_email()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'my-password',
            'password_confirmation' => 'my-password'
        ]);

        $response->assertSessionHasErrors('email')
            ->assertStatus(302);

        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe',
            'email' => 'invalid-email'
        ]);
    }

    /**
     * A user cannot register with an existing email
     *
     * @return void
     */
    public function test_user_cannot_register_with_an_existing_email()
    {
        $user = User::factory()->create();

        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => $user->email,
            'password' => 'my-password',
            'password_confirmation' => 'my-password'
        ]);

        $response->assertSessionHasErrors('email')
            ->assertStatus(302);

        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe',
            'email' => $user->email
        ]);
    }

    /**
     * A user can register
     *
     * @return void
     */
    public function test_user_can_register()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@contoso.com',
            'password' => 'my-password',
            'password_confirmation' => 'my-password'
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@contoso.com'
        ]);
    }
}
