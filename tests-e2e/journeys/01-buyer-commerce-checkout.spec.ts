import { test, expect } from '@playwright/test';
import { loginBuyer } from '../helpers/api-client';

test.describe('E2E Journey 1: Buyer Marketplace Purchase & Atomic Checkout', () => {
  test('Complete purchase journey: browse ➔ add to cart ➔ checkout ➔ order history', async ({ request }) => {
    // 1. Authenticate as Buyer
    const buyer = await loginBuyer(request);
    expect(buyer.token).toBeTruthy();

    // 2. Browse Public Catalog to find an in-stock product
    const catalogRes = await request.get('/api/v1/products');
    expect(catalogRes.ok()).toBeTruthy();
    const catalogJson = await catalogRes.json();
    const products = catalogJson.data || catalogJson;
    expect(products.length).toBeGreaterThan(0);

    const targetProduct = products.find((p: any) => p.stock > 0);
    expect(targetProduct, 'At least one product must be in stock').toBeDefined();

    // 3. Reset Cart to a clean state
    await request.delete('/api/v1/cart', { headers: buyer.headers });

    // 4. Add the product to cart
    const addCartRes = await request.post(`/api/v1/cart/items/${targetProduct.id}`, {
      headers: buyer.headers,
      data: { quantity: 1 },
    });
    expect([200, 201]).toContain(addCartRes.status());

    // 5. Inspect Cart to verify item was added
    const cartRes = await request.get('/api/v1/cart', { headers: buyer.headers });
    expect(cartRes.status()).toBe(200);
    const cartJson = await cartRes.json();
    const cartItems = cartJson.data?.items || cartJson.items || [];
    expect(cartItems.length).toBeGreaterThan(0);

    // 6. Execute Atomic Checkout
    const checkoutRes = await request.post('/api/v1/checkout', {
      headers: buyer.headers,
      data: {
        shipping_address: '123 Avenue Mohammed V, Marrakech 40000',
        payment_method: 'cash',
        notes: 'Please call before delivery',
      },
    });
    expect(checkoutRes.status()).toBe(201);
    const checkoutJson = await checkoutRes.json();
    const createdOrders = Array.isArray(checkoutJson.data) ? checkoutJson.data : [checkoutJson.data || checkoutJson];
    expect(createdOrders.length).toBeGreaterThan(0);
    const order = createdOrders[0];
    expect(order.id).toBeDefined();

    // 7. Verify Order appears in Buyer Order History
    const ordersRes = await request.get('/api/v1/buyer/orders', { headers: buyer.headers });
    expect(ordersRes.status()).toBe(200);
    const ordersJson = await ordersRes.json();
    const ordersList = ordersJson.data || ordersJson;
    const foundOrder = ordersList.find((o: any) => o.id === order.id);
    expect(foundOrder).toBeDefined();
  });
});

