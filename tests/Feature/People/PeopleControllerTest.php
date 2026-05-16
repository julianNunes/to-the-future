<?php

namespace Tests\Feature\People;

use App\Models\People;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PeopleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testPeopleIndexFiltersBySearchAndIgnoresUnknownSort(): void
    {
        $user = User::factory()->create();

        People::query()->create([
            'name' => 'Alpha Search Person',
            'gender' => 'F',
        ]);
        People::query()->create([
            'name' => 'Omega Other Person',
            'gender' => 'M',
        ]);

        $url = route('people.index', [
            'search' => 'Alpha',
            'sort' => [
                'key' => 'invalid_column',
                'order' => 'desc',
            ],
        ], false);

        $response = $this->actingAs($user)->get($url);

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
                ->component('People/Index')
                ->has('data.data', 1)
                ->where('data.data.0.name', 'Alpha Search Person'));
    }

    public function testPeopleCrudFlowPersistsThroughRoutes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/people', [
                'name' => 'Maria Silva',
                'gender' => 'F',
                'email' => 'maria@example.com',
                'phone' => '11999990000',
                'address' => 'Rua 1',
            ])
            ->assertRedirect();

        $person = People::query()->where('name', 'Maria Silva')->firstOrFail();

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'email' => 'maria@example.com',
        ]);

        $this->actingAs($user)
            ->put("/people/{$person->id}", [
                'name' => 'Maria Souza',
                'gender' => 'F',
                'email' => 'maria.souza@example.com',
                'phone' => '11999991111',
                'address' => 'Rua 2',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'name' => 'Maria Souza',
            'email' => 'maria.souza@example.com',
        ]);

        $this->actingAs($user)
            ->delete("/people/{$person->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('people', [
            'id' => $person->id,
        ]);
    }
}