<?php

namespace Tests\Feature\Provision;

use App\Models\Provision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_an_owned_provision(): void
    {
        $owner = User::query()->create([
            'name' => 'Provision Owner',
            'email' => 'provision-owner@example.com',
            'password' => 'password-owner',
        ]);

        $provision = Provision::query()->create([
            'description' => 'Owner Provision',
            'value' => 100,
            'group' => 'MONTHLY',
            'remarks' => 'Owner Remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->from('/provision')
            ->delete('/provision/' . $provision->id);

        $response->assertRedirect('/provision');
        $response->assertSessionHas('success', 'default.sucess-delete');
        $this->assertDatabaseMissing('provisions', ['id' => $provision->id]);
    }

    public function test_intruder_cannot_update_another_users_provision(): void
    {
        $owner = User::query()->create([
            'name' => 'Provision Target Owner',
            'email' => 'provision-target-owner@example.com',
            'password' => 'password-target-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Provision Intruder',
            'email' => 'provision-intruder@example.com',
            'password' => 'password-intruder',
        ]);

        $provision = Provision::query()->create([
            'description' => 'Protected Provision',
            'value' => 200,
            'group' => 'MONTHLY',
            'remarks' => 'Protected Remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)->put('/provision/' . $provision->id, [
            'description' => 'Intruder Change',
            'value' => '250',
            'group' => 'WEEK_2',
            'remarks' => 'Attempt',
            'tags' => [],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('provisions', [
            'id' => $provision->id,
            'description' => 'Protected Provision',
            'value' => 200,
            'group' => 'MONTHLY',
            'remarks' => 'Protected Remark',
            'user_id' => $owner->id,
        ]);
    }

    public function test_intruder_cannot_delete_another_users_provision(): void
    {
        $owner = User::query()->create([
            'name' => 'Provision Delete Owner',
            'email' => 'provision-delete-owner@example.com',
            'password' => 'password-delete-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Provision Delete Intruder',
            'email' => 'provision-delete-intruder@example.com',
            'password' => 'password-delete-intruder',
        ]);

        $provision = Provision::query()->create([
            'description' => 'Delete Protected Provision',
            'value' => 300,
            'group' => 'MONTHLY',
            'remarks' => 'Delete Protected Remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)->delete('/provision/' . $provision->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('provisions', [
            'id' => $provision->id,
            'description' => 'Delete Protected Provision',
            'user_id' => $owner->id,
        ]);
    }
}
