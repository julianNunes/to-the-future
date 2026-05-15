<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestsAreRedirectedFromProtectedDomainPages(): void
    {
        foreach ([
            '/dashboard',
            '/budget/2026',
            '/credit-card',
            '/prepaid-card',
            '/tag',
        ] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function testGuestsAreRedirectedFromProtectedDomainWriteEndpoints(): void
    {
        $this->post('/tag', ['name' => 'SECURITY'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('tags', ['name' => 'SECURITY']);
    }

    public function testAuthenticatedUsersCanAccessProtectedDomainPages(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/dashboard',
            '/budget/2026',
            '/credit-card',
            '/prepaid-card',
            '/tag',
        ] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk();
        }
    }

    public function testAuthenticatedUsersCanUseProtectedWriteEndpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/tag')
            ->post('/tag', ['name' => 'SECURITY'])
            ->assertRedirect('/tag');

        $this->assertDatabaseHas('tags', [
            'name' => 'SECURITY',
            'user_id' => $user->id,
        ]);
    }
}
