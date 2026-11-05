<?php

namespace Modules\Store\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Store\Models\Store;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtisanStoreApiTest extends TestCase
{
    use RefreshDatabase;

    private User $artisanUser;

    private Artisan $artisan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);
        Role::firstOrCreate(['name' => 'admin']);

        $this->artisanUser = User::factory()->create();
        $this->artisanUser->assignRole('artisan');

        $this->artisan = Artisan::factory()->create([
            'user_id' => $this->artisanUser->id,
            'status' => 'active',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_artisan_store(): void
    {
        $response = $this->getJson('/api/v1/artisan/store');

        $response->assertStatus(401);
    }

    public function test_non_artisan_cannot_access_artisan_store(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');
        Sanctum::actingAs($buyer);

        $response = $this->getJson('/api/v1/artisan/store');

        $response->assertStatus(403);
    }

    public function test_artisan_gets_404_when_no_store_exists_yet(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson('/api/v1/artisan/store');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No store has been created yet.',
                'data' => null,
            ]);
    }

    public function test_artisan_can_view_their_existing_store(): void
    {
        $store = Store::factory()->create([
            'artisan_id' => $this->artisan->id,
            'name' => 'Atlas Pottery',
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson('/api/v1/artisan/store');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $store->id)
            ->assertJsonPath('data.name', 'Atlas Pottery');
    }

    public function test_artisan_can_create_a_store_with_logo(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $logo = UploadedFile::fake()->image('store-logo.jpg', 400, 400);

        $response = $this->postJson('/api/v1/artisan/store', [
            'name' => 'Berber Carpets',
            'description' => 'Authentic handmade Moroccan rugs.',
            'logo' => $logo,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Berber Carpets')
            ->assertJsonPath('data.slug', 'berber-carpets');

        $this->assertDatabaseHas('stores', [
            'artisan_id' => $this->artisan->id,
            'name' => 'Berber Carpets',
            'slug' => 'berber-carpets',
        ]);

        $createdStore = Store::first();
        $this->assertNotNull($createdStore->logo);
        Storage::disk('public')->assertExists($createdStore->logo);
    }

    public function test_artisan_cannot_create_multiple_stores(): void
    {
        Store::factory()->create([
            'artisan_id' => $this->artisan->id,
            'name' => 'First Store',
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->postJson('/api/v1/artisan/store', [
            'name' => 'Second Store',
        ]);

        $response->assertStatus(403);
    }

    public function test_artisan_can_update_store_details_and_logo(): void
    {
        $store = Store::factory()->create([
            'artisan_id' => $this->artisan->id,
            'name' => 'Old Brand',
            'description' => 'Old description',
        ]);

        Sanctum::actingAs($this->artisanUser);

        $newLogo = UploadedFile::fake()->image('new-logo.png', 300, 300);

        $response = $this->postJson('/api/v1/artisan/store/update', [
            'name' => 'Updated Brand',
            'description' => 'Updated description',
            'logo' => $newLogo,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Brand')
            ->assertJsonPath('data.slug', 'updated-brand');

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Updated Brand',
            'slug' => 'updated-brand',
        ]);
    }

    public function test_artisan_can_delete_store_logo(): void
    {
        $logoPath = 'stores/logos/test-logo.jpg';
        Storage::disk('public')->put($logoPath, 'fake-content');

        $store = Store::factory()->create([
            'artisan_id' => $this->artisan->id,
            'logo' => $logoPath,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->deleteJson('/api/v1/artisan/store/logo');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Store logo removed successfully.',
            ]);

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'logo' => null,
        ]);

        Storage::disk('public')->assertMissing($logoPath);
    }

    public function test_slug_uniqueness_is_automatically_maintained(): void
    {
        Store::factory()->create([
            'name' => 'Unique Store',
            'slug' => 'unique-store',
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->postJson('/api/v1/artisan/store', [
            'name' => 'Unique Store',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 'unique-store-1');
    }
}

