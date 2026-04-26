<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Artisan;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtisanOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_can_mark_processing_order_as_shipped(): void
    {
        $artisan = Artisan::factory()->create();
        $order = Order::factory()->create([
            'artisan_id' => $artisan->id,
            'status' => OrderStatus::Processing,
        ]);

        $response = $this->actingAs($artisan->user)->patch(route('artisan.orders.status', $order), [
            'status' => 'shipped',
        ]);

        $response->assertRedirect();
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
    }

    public function test_artisan_can_mark_shipped_order_as_delivered(): void
    {
        $artisan = Artisan::factory()->create();
        $order = Order::factory()->create([
            'artisan_id' => $artisan->id,
            'status' => OrderStatus::Shipped,
        ]);

        $response = $this->actingAs($artisan->user)->patch(route('artisan.orders.status', $order), [
            'status' => 'delivered',
        ]);

        $response->assertRedirect();
        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
    }

    public function test_artisan_cannot_skip_directly_to_delivered_from_processing(): void
    {
        $artisan = Artisan::factory()->create();
        $order = Order::factory()->create([
            'artisan_id' => $artisan->id,
            'status' => OrderStatus::Processing,
        ]);

        $response = $this->actingAs($artisan->user)->from(route('artisan.orders.show', $order))
            ->patch(route('artisan.orders.status', $order), [
                'status' => 'delivered',
            ]);

        $response->assertRedirect(route('artisan.orders.show', $order));
        $response->assertSessionHas('error');
        $this->assertSame(OrderStatus::Processing, $order->fresh()->status);
    }
}
