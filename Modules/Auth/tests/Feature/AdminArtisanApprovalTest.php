<?php

namespace Modules\Auth\Tests\Feature;

use App\Enums\ArtisanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Events\ArtisanApproved;
use Modules\Auth\Events\ArtisanRejected;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminArtisanApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $artisanUser;

    private Artisan $artisan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'artisan']);
        Role::firstOrCreate(['name' => 'buyer']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->artisanUser = User::factory()->create(['name' => 'Pending Artisan']);
        $this->artisanUser->assignRole('artisan');

        $this->artisan = Artisan::factory()->create([
            'user_id' => $this->artisanUser->id,
            'status' => ArtisanStatus::Pending,
            'craft_type' => 'Zellij & Mosaics',
            'city' => 'Fes',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_admin_artisan_endpoints(): void
    {
        $response = $this->getJson('/api/v1/admin/artisans');

        $response->assertUnauthorized();
    }

    public function test_buyer_cannot_access_admin_artisan_endpoints(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');
        Sanctum::actingAs($buyer);

        $response = $this->getJson('/api/v1/admin/artisans');

        $response->assertForbidden();
    }

    public function test_artisan_cannot_access_admin_artisan_endpoints(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson('/api/v1/admin/artisans');

        $response->assertForbidden();
    }

    public function test_admin_can_list_artisans_with_pagination(): void
    {
        Sanctum::actingAs($this->adminUser);

        Artisan::factory()->count(4)->create(['status' => ArtisanStatus::Active]);

        $response = $this->getJson('/api/v1/admin/artisans?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'name',
                        'email',
                        'status',
                        'craft_type',
                        'city',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_admin_can_filter_artisans_by_status(): void
    {
        Sanctum::actingAs($this->adminUser);

        Artisan::factory()->count(2)->create(['status' => ArtisanStatus::Active]);

        $response = $this->getJson('/api/v1/admin/artisans?status=pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', ArtisanStatus::Pending->value);
    }

    public function test_admin_can_search_artisans_by_name_or_city(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/admin/artisans?search=Zellij');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.craft_type', 'Zellij & Mosaics');
    }

    public function test_admin_can_approve_pending_artisan_and_event_fires(): void
    {
        Event::fake([ArtisanApproved::class, ArtisanRejected::class]);
        Sanctum::actingAs($this->adminUser);

        $response = $this->patchJson("/api/v1/admin/artisans/{$this->artisan->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Artisan has been approved successfully.')
            ->assertJsonPath('data.status', ArtisanStatus::Active->value);

        $this->assertEquals(ArtisanStatus::Active, $this->artisan->fresh()->status);

        Event::assertDispatched(ArtisanApproved::class, function (ArtisanApproved $event) {
            return $event->artisan->id === $this->artisan->id;
        });

        Event::assertNotDispatched(ArtisanRejected::class);
    }

    public function test_admin_can_reject_artisan_and_event_fires(): void
    {
        Event::fake([ArtisanApproved::class, ArtisanRejected::class]);
        Sanctum::actingAs($this->adminUser);

        $response = $this->patchJson("/api/v1/admin/artisans/{$this->artisan->id}/status", [
            'status' => 'rejected',
            'reason' => 'Incomplete artisanal certificate',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Artisan application has been rejected.')
            ->assertJsonPath('data.status', ArtisanStatus::Rejected->value);

        $this->assertEquals(ArtisanStatus::Rejected, $this->artisan->fresh()->status);

        Event::assertDispatched(ArtisanRejected::class, function (ArtisanRejected $event) {
            return $event->artisan->id === $this->artisan->id
                && $event->reason === 'Incomplete artisanal certificate';
        });

        Event::assertNotDispatched(ArtisanApproved::class);
    }

    public function test_non_admin_cannot_update_artisan_status(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $response = $this->patchJson("/api/v1/admin/artisans/{$this->artisan->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->patchJson("/api/v1/admin/artisans/{$this->artisan->id}/status", [
            'status' => 'invalid_status_choice',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
