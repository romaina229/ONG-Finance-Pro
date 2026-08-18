<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_use_sanctum_token_for_me_endpoint(): void
    {
        $user = User::create([
            'name' => 'Finance Pro Test',
            'email' => 'test@finance-pro.local',
            'password' => 'Password!123',
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password!123',
        ]);

        $login->assertOk()
            ->assertJsonStructure(['token', 'user' ]);

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }
}
