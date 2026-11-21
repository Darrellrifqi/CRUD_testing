<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Return a valid payload suitable for creating a courier. */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Budiono Hadi Agung',
            'phone'         => '081234567890',
            'email'         => 'budi@example.com',
            'nik'           => '1234567890123456',
            'address'       => 'Jl. Merdeka No. 1',
            'city'          => 'Jakarta',
            'vehicle_type'  => 'motorcycle',
            'vehicle_plate' => 'B 1234 ABC',
            'level'         => 2,
            'is_active'     => true,
            'registered_at' => '2024-01-15',
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // INDEX — pagination
    // -----------------------------------------------------------------------

    /** @test */
    public function index_returns_paginated_results(): void
    {
        Courier::factory()->count(20)->create();

        $response = $this->getJson('/api/couriers');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'phone', 'email', 'level']],
                'links',
                'meta' => ['total', 'per_page', 'current_page'],
            ]);

        $this->assertCount(15, $response->json('data')); // default per_page = 15
    }

    /** @test */
    public function index_respects_per_page_query_parameter(): void
    {
        Courier::factory()->count(10)->create();

        $response = $this->getJson('/api/couriers?per_page=5');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
    }

    // -----------------------------------------------------------------------
    // INDEX — default sort (name)
    // -----------------------------------------------------------------------

    /** @test */
    public function index_sorts_by_name_by_default(): void
    {
        Courier::factory()->create(['name' => 'Zara']);
        Courier::factory()->create(['name' => 'Agus']);
        Courier::factory()->create(['name' => 'Maya']);

        $names = $this->getJson('/api/couriers?per_page=100')
            ->assertOk()
            ->json('data.*.name');

        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, $names);
    }

    // -----------------------------------------------------------------------
    // INDEX — sort by registered_at
    // -----------------------------------------------------------------------

    /** @test */
    public function index_can_sort_by_registered_at(): void
    {
        Courier::factory()->create(['registered_at' => '2024-06-01']);
        Courier::factory()->create(['registered_at' => '2023-01-10']);
        Courier::factory()->create(['registered_at' => '2025-03-15']);

        $dates = $this->getJson('/api/couriers?sort_by=registered_at&per_page=100')
            ->assertOk()
            ->json('data.*.registered_at');

        $sorted = $dates;
        sort($sorted);

        $this->assertSame($sorted, $dates);
    }

    // -----------------------------------------------------------------------
    // INDEX — search
    // -----------------------------------------------------------------------

    /** @test */
    public function index_search_matches_all_tokens_in_any_position(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung']);
        Courier::factory()->create(['name' => 'Budi Santoso']);
        Courier::factory()->create(['name' => 'Agung Prasetyo']);

        $response = $this->getJson('/api/couriers?search=budi+agung&per_page=100');

        $response->assertOk();
        $names = $response->json('data.*.name');

        // Only "Budiono Hadi Agung" contains both "budi" AND "agung"
        $this->assertCount(1, $names);
        $this->assertContains('Budiono Hadi Agung', $names);
    }

    /** @test */
    public function index_search_is_case_insensitive(): void
    {
        Courier::factory()->create(['name' => 'Siti Rahayu']);
        Courier::factory()->create(['name' => 'Rahmat Santoso']);

        $names = $this->getJson('/api/couriers?search=SITI&per_page=100')
            ->assertOk()
            ->json('data.*.name');

        $this->assertCount(1, $names);
        $this->assertContains('Siti Rahayu', $names);
    }

    // -----------------------------------------------------------------------
    // INDEX — level filter
    // -----------------------------------------------------------------------

    /** @test */
    public function index_filters_by_single_level(): void
    {
        Courier::factory()->level(1)->count(3)->create();
        Courier::factory()->level(2)->count(2)->create();
        Courier::factory()->level(3)->count(4)->create();

        $data = $this->getJson('/api/couriers?level=2&per_page=100')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $data);
        $this->assertSame([2, 2], array_column($data, 'level'));
    }

    /** @test */
    public function index_filters_by_multiple_levels(): void
    {
        Courier::factory()->level(1)->count(3)->create();
        Courier::factory()->level(2)->count(2)->create();
        Courier::factory()->level(3)->count(4)->create();
        Courier::factory()->level(4)->count(1)->create();

        $data = $this->getJson('/api/couriers?level=2,3&per_page=100')
            ->assertOk()
            ->json('data');

        $this->assertCount(6, $data);

        $levels = array_unique(array_column($data, 'level'));
        sort($levels);
        $this->assertSame([2, 3], $levels);
    }

    /** @test */
    public function index_ignores_invalid_levels_in_filter(): void
    {
        Courier::factory()->level(1)->count(2)->create();

        // Level 99 is invalid; only level 1 results are expected
        $data = $this->getJson('/api/couriers?level=1,99&per_page=100')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $data);
    }

    // -----------------------------------------------------------------------
    // STORE
    // -----------------------------------------------------------------------

    /** @test */
    public function store_creates_a_courier_and_persists_to_database(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/couriers', $payload);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Budiono Hadi Agung', 'level' => 2]);

        $this->assertDatabaseHas('couriers', [
            'name'  => 'Budiono Hadi Agung',
            'email' => 'budi@example.com',
            'level' => 2,
        ]);
    }

    /** @test */
    public function store_returns_422_when_required_fields_are_missing(): void
    {
        $this->postJson('/api/couriers', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'email', 'nik', 'vehicle_type', 'vehicle_plate', 'level', 'registered_at']);
    }

    /** @test */
    public function store_returns_422_for_invalid_level(): void
    {
        $this->postJson('/api/couriers', $this->validPayload(['level' => 6]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['level']);
    }

    /** @test */
    public function store_returns_422_for_invalid_vehicle_type(): void
    {
        $this->postJson('/api/couriers', $this->validPayload(['vehicle_type' => 'rocket']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['vehicle_type']);
    }

    /** @test */
    public function store_returns_422_for_duplicate_email(): void
    {
        Courier::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/couriers', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function store_returns_422_for_duplicate_nik(): void
    {
        Courier::factory()->create(['nik' => '1234567890123456']);

        $this->postJson('/api/couriers', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nik']);
    }

    // -----------------------------------------------------------------------
    // SHOW
    // -----------------------------------------------------------------------

    /** @test */
    public function show_returns_all_courier_fields(): void
    {
        $courier = Courier::factory()->create();

        $response = $this->getJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'phone', 'email', 'nik',
                    'address', 'city', 'vehicle_type', 'vehicle_plate',
                    'level', 'is_active', 'rating', 'total_deliveries',
                    'registered_at', 'created_at', 'updated_at',
                ],
            ]);

        $this->assertSame($courier->id, $response->json('data.id'));
    }

    /** @test */
    public function show_returns_404_for_nonexistent_courier(): void
    {
        $this->getJson('/api/couriers/999999')->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // UPDATE
    // -----------------------------------------------------------------------

    /** @test */
    public function update_modifies_courier_and_persists_changes(): void
    {
        $courier = Courier::factory()->create(['level' => 1]);

        $response = $this->patchJson("/api/couriers/{$courier->id}", ['level' => 3, 'city' => 'Surabaya']);

        $response->assertOk()
            ->assertJsonFragment(['level' => 3, 'city' => 'Surabaya']);

        $this->assertDatabaseHas('couriers', [
            'id'    => $courier->id,
            'level' => 3,
            'city'  => 'Surabaya',
        ]);
    }

    /** @test */
    public function update_returns_422_for_invalid_level(): void
    {
        $courier = Courier::factory()->create();

        $this->patchJson("/api/couriers/{$courier->id}", ['level' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['level']);
    }

    /** @test */
    public function update_allows_same_unique_values_for_own_record(): void
    {
        $courier = Courier::factory()->create(['email' => 'owner@example.com']);

        // Sending own e-mail back must pass unique check
        $this->patchJson("/api/couriers/{$courier->id}", ['email' => 'owner@example.com'])
            ->assertOk();
    }

    /** @test */
    public function update_returns_422_when_email_conflicts_with_another_courier(): void
    {
        $other = Courier::factory()->create(['email' => 'taken@example.com']);
        $courier = Courier::factory()->create();

        $this->patchJson("/api/couriers/{$courier->id}", ['email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // -----------------------------------------------------------------------
    // DESTROY
    // -----------------------------------------------------------------------

    /** @test */
    public function destroy_soft_deletes_courier_and_it_disappears_from_listing(): void
    {
        $courier = Courier::factory()->create();

        $this->deleteJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Courier deleted successfully.']);

        // Record should be soft-deleted (still in DB but with deleted_at set)
        $this->assertSoftDeleted('couriers', ['id' => $courier->id]);

        // Should not appear in index listing
        $data = $this->getJson('/api/couriers?per_page=100')->json('data');
        $ids = array_column($data, 'id');
        $this->assertNotContains($courier->id, $ids);
    }

    /** @test */
    public function destroy_returns_404_for_nonexistent_courier(): void
    {
        $this->deleteJson('/api/couriers/999999')->assertNotFound();
    }
}
