<?php

namespace Tests\Feature\Http\Requests;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreRequiresName(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/tag')
            ->post('/tag', ['name' => ''])
            ->assertRedirect('/tag')
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('tags', 0);
    }

    public function testStorePersistsTrimmedName(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/tag')
            ->post('/tag', ['name' => '  SECURITY  '])
            ->assertRedirect('/tag');

        $this->assertDatabaseHas('tags', [
            'name' => 'SECURITY',
            'user_id' => $user->id,
        ]);
    }

    public function testUpdateRequiresName(): void
    {
        $user = User::factory()->create();
        $tag = Tag::query()->create([
            'name' => 'SECURITY',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/tag')
            ->put("/tag/{$tag->id}", ['name' => ''])
            ->assertRedirect('/tag')
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'SECURITY',
        ]);
    }

    public function testUpdatePersistsTrimmedName(): void
    {
        $user = User::factory()->create();
        $tag = Tag::query()->create([
            'name' => 'SECURITY',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/tag')
            ->put("/tag/{$tag->id}", ['name' => '  SAVINGS  '])
            ->assertRedirect('/tag');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'SAVINGS',
            'user_id' => $user->id,
        ]);
    }
}
