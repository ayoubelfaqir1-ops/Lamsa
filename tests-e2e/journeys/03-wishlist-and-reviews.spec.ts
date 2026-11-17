import { test, expect } from '@playwright/test';
import { loginBuyer } from '../helpers/api-client';

test.describe('E2E Journey 3: Wishlist & Reviews Workflow', () => {
  test('Complete wishlist & review journey: browse ➔ toggle favorite ➔ inspect wishlist ➔ read reviews', async ({ request }) => {
    // 1. Authenticate as Buyer
    const buyer = await loginBuyer(request);
    expect(buyer.token).toBeTruthy();

    // 2. Discover Public Products
    const productsRes = await request.get('/api/v1/products');
    expect(productsRes.status()).toBe(200);
    const productsJson = await productsRes.json();
    const products = productsJson.data || productsJson;
    expect(products.length).toBeGreaterThan(0);

    const targetProduct = products[0];
    expect(targetProduct.id).toBeDefined();

    // 3. Toggle Favorite (Bookmark the product)
    const toggleRes = await request.post(`/api/v1/products/${targetProduct.id}/favorite`, {
      headers: buyer.headers,
    });
    expect([200, 201]).toContain(toggleRes.status());

    // 4. Inspect Buyer Wishlist
    const wishlistRes = await request.get('/api/v1/favorites', {
      headers: buyer.headers,
    });
    expect(wishlistRes.status()).toBe(200);
    const wishlistJson = await wishlistRes.json();
    const favorites = wishlistJson.data || wishlistJson;
    expect(Array.isArray(favorites)).toBeTruthy();

    // 5. Inspect Product Reviews
    const reviewsRes = await request.get(`/api/v1/products/${targetProduct.id}/reviews`);
    expect(reviewsRes.status()).toBe(200);
    const reviewsJson = await reviewsRes.json();
    expect(reviewsJson).toBeDefined();

    // 6. Clean up: Toggle favorite again to return to initial state
    await request.delete(`/api/v1/products/${targetProduct.id}/favorite`, {
      headers: buyer.headers,
    });
  });
});

