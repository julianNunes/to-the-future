<?php

namespace Tests\Feature\Http\Requests;

use App\Models\Provision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/provision')
            ->post('/provision', [
                'description' => '',
                'value' => '',
                'group' => '',
            ])
            ->assertRedirect('/provision')
            ->assertSessionHasErrors(['description', 'value', 'group']);

        $this->assertDatabaseCount('provisions', 0);
    }

    public function testStoreRejectsInvalidGroup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/provision')
            ->post('/provision', [
                'description' => 'Travel Reserve',
                'value' => '150',
                'group' => 'YEARLY',
            ])
            ->assertRedirect('/provision')
            ->assertSessionHasErrors(['group']);

        $this->assertDatabaseCount('provisions', 0);
    }

    public function testStoreNormalizesAndPersistsProvisionPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();

        $this->actingAs($user)
            ->from('/provision')
            ->post('/provision', [
                'description' => '  Travel  ',
                'value' => 'R$ 300,20',
                'group' => 'MONTHLY',
                'remarks' => '  Reserve  ',
                'share_value' => 'R$ 120,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'TRIP'],
                ],
            ])
            ->assertRedirect('/provision');

        $provision = Provision::query()->with('tags')->firstOrFail();

        $this->assertSame('Travel', $provision->description);
        $this->assertSame('MONTHLY', $provision->group);
        $this->assertSame('Reserve', $provision->remarks);
        $this->assertSame($user->id, $provision->user_id);
        $this->assertSame($shareUser->id, $provision->share_user_id);
        $this->assertSame(['TRIP'], $provision->tags->pluck('name')->all());
        $this->assertEquals(300.20, (float) $provision->value);
        $this->assertEquals(120.20, (float) $provision->share_value);
    }

    public function testUpdateRequiresShareUserWhenShareValueIsProvided(): void
    {
        $user = User::factory()->create();
        $provision = Provision::query()->create([
            'description' => 'Emergency',
            'value' => 250,
            'group' => 'MONTHLY',
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/provision')
            ->put("/provision/{$provision->id}", [
                'description' => 'Emergency',
                'value' => '250',
                'group' => 'MONTHLY',
                'share_value' => 'R$ 80,00',
                'share_user_id' => '',
            ])
            ->assertRedirect('/provision')
            ->assertSessionHasErrors(['share_user_id']);
    }

    public function testUpdateNormalizesAndPersistsProvisionPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $provision = Provision::query()->create([
            'description' => 'Emergency',
            'value' => 250,
            'group' => 'MONTHLY',
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/provision')
            ->put("/provision/{$provision->id}", [
                'description' => '  Updated Emergency  ',
                'value' => 'R$ 410,70',
                'group' => 'WEEK_2',
                'remarks' => '  Shared  ',
                'share_value' => 'R$ 205,35',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'SAFETY'],
                ],
            ])
            ->assertRedirect('/provision');

        $provision->refresh()->load('tags');

        $this->assertSame('Updated Emergency', $provision->description);
        $this->assertSame('WEEK_2', $provision->group);
        $this->assertSame('Shared', $provision->remarks);
        $this->assertSame($shareUser->id, $provision->share_user_id);
        $this->assertSame(['SAFETY'], $provision->tags->pluck('name')->all());
        $this->assertEquals(410.70, (float) $provision->value);
        $this->assertEquals(205.35, (float) $provision->share_value);
    }
}
