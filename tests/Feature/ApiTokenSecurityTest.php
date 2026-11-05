<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function loginAndGetToken(User $user): string
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        return $response->json('token');
    }

    /** Guard di-cache antar HTTP call dalam satu test; lupakan agar mirip production (fresh per request). */
    protected function freshAuth(): void
    {
        $this->app['auth']->forgetGuards();
    }

    public function test_login_token_can_access_protected_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $token = $this->loginAndGetToken($user);

        $this->freshAuth();
        $this->withToken($token)->getJson('/api/v1/user')->assertStatus(200);
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $tokenA = $this->loginAndGetToken($user);
        $this->freshAuth();
        $tokenB = $this->loginAndGetToken($user);

        $this->freshAuth();
        $this->withToken($tokenA)->postJson('/api/v1/logout')->assertStatus(200);

        // Token A mati, token B (perangkat lain) tetap hidup.
        $this->freshAuth();
        $this->withToken($tokenA)->getJson('/api/v1/user')->assertStatus(401);
        $this->freshAuth();
        $this->withToken($tokenB)->getJson('/api/v1/user')->assertStatus(200);
    }

    public function test_tokens_list_does_not_leak_plaintext(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $token = $this->loginAndGetToken($user);

        $response = $this->withToken($token)->getJson('/api/v1/tokens')->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertArrayNotHasKey('token', $data[0]);
        $this->assertTrue(collect($data)->contains(fn ($t) => $t['is_current'] === true));
    }

    public function test_revoke_all_tokens_kills_every_session(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $tokenA = $this->loginAndGetToken($user);
        $this->freshAuth();
        $tokenB = $this->loginAndGetToken($user);

        $this->freshAuth();
        $this->withToken($tokenA)->deleteJson('/api/v1/tokens')->assertStatus(200);

        $this->freshAuth();
        $this->withToken($tokenA)->getJson('/api/v1/user')->assertStatus(401);
        $this->freshAuth();
        $this->withToken($tokenB)->getJson('/api/v1/user')->assertStatus(401);
    }

    public function test_expired_token_is_rejected(): void
    {
        config()->set('sanctum.expiration', 60);

        $user = User::factory()->create(['role' => 'kasir']);
        $token = $this->loginAndGetToken($user);

        // Mundurkan created_at token melewati masa berlaku.
        $user->tokens()->update(['created_at' => now()->subMinutes(61)]);

        $this->withToken($token)->getJson('/api/v1/user')->assertStatus(401);
    }

    public function test_login_still_rejects_inactive_user(): void
    {
        $user = User::factory()->create(['role' => 'kasir', 'active' => false]);

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422);
    }
}
