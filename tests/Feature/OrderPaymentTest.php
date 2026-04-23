<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_only_accepts_cash_or_card_payment_methods(): void
    {
        $buyer = User::factory()->buyer()->create([
            'phone' => '0612345678',
            'address' => 'Test address',
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.store'), [
            'shipping_address' => 'Test shipping address',
            'payment_method' => 'bank_transfer',
            'items' => [['product_id' => 999, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_cash_on_delivery_checkout_moves_new_orders_to_processing(): void
    {
        $buyer = User::factory()->buyer()->create([
            'phone' => '0612345678',
            'address' => 'Test address',
        ]);
        $artisan = \App\Models\Artisan::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $product = \App\Models\Product::factory()->create([
            'artisan_id' => $artisan->id,
            'category_id' => $category->id,
            'is_published' => true,
            'stock' => 5,
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.store'), [
            'shipping_address' => 'Test shipping address',
            'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertRedirect(route('orders.index'));

        $order = Order::query()->where('user_id', $buyer->id)->latest('id')->firstOrFail();

        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertSame('cash', $order->payment_method);
        $this->assertSame('cash_on_delivery', $order->payment_status);
    }

    public function test_card_checkout_keeps_new_orders_pending_until_card_confirmation(): void
    {
        $buyer = User::factory()->buyer()->create([
            'phone' => '0612345678',
            'address' => 'Test address',
        ]);
        $artisan = \App\Models\Artisan::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $product = \App\Models\Product::factory()->create([
            'artisan_id' => $artisan->id,
            'category_id' => $category->id,
            'is_published' => true,
            'stock' => 5,
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.store'), [
            'shipping_address' => 'Test shipping address',
            'payment_method' => 'card',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $order = Order::query()->where('user_id', $buyer->id)->latest('id')->firstOrFail();

        $response->assertRedirect(route('orders.payment.card', ['orders' => $order->id]));
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('card', $order->payment_method);
        $this->assertSame('unpaid', $order->payment_status);
    }

    public function test_cash_on_delivery_confirms_pending_orders(): void
    {
        $buyer = User::factory()->buyer()->create();
        $orders = Order::factory()->count(2)->create([
            'user_id' => $buyer->id,
            'status' => OrderStatus::Pending,
            'payment_method' => 'card',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.payment.card.process'), [
            'orders' => $orders->pluck('id')->implode(','),
        ]);

        $response->assertRedirect(route('orders.index'));

        foreach ($orders as $order) {
            $order->refresh();

            $this->assertSame(OrderStatus::Processing, $order->status);
            $this->assertSame('card', $order->payment_method);
            $this->assertSame('paid', $order->payment_status);
        }
    }

    public function test_card_payment_confirms_pending_orders_as_paid(): void
    {
        $buyer = User::factory()->buyer()->create();
        $orders = Order::factory()->count(2)->create([
            'user_id' => $buyer->id,
            'status' => OrderStatus::Pending,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.payment.card.process'), [
            'orders' => $orders->pluck('id')->implode(','),
        ]);

        $response->assertRedirect(route('orders.index'));

        foreach ($orders as $order) {
            $order->refresh();

            $this->assertSame(OrderStatus::Processing, $order->status);
            $this->assertSame('card', $order->payment_method);
            $this->assertSame('paid', $order->payment_status);
        }
    }
}
