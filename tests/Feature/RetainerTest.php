<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RetainerTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_retainer_is_visible_on_the_index(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create();

        $response = $this->actingAs($user)->post(route('retainers.store'), [
            'client_id' => $client->id,
            'total_amount' => 12000,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'description' => 'Annual support agreement',
        ]);

        $response->assertRedirect(route('retainers.index'));
        $this->assertDatabaseHas('retainers', [
            'client_id' => $client->id,
            'reference_number' => 'RET-000001',
            'created_by' => $user->id,
        ]);

        $this->get(route('retainers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Retainers/Index')
                ->where('retainers.data.0.reference_number', 'RET-000001')
                ->where('retainers.data.0.client_id', $client->id)
                ->where('retainers.data.0.client_name', $client->name)
                ->where('summary.count', 1)
            );

        $this->assertSame(1, Retainer::count());
    }
}