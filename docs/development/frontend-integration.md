# Frontend Integration & Client Guide

> Complete integration manual for frontend engineers building Web (Next.js/React) or Mobile (Flutter/React Native) clients for Lamsa.ma.

---

## 1. API Architecture & Standards

- **Base URL (Local):** `http://127.0.0.1:8000/api/v1`
- **Base URL (Production):** `https://api.lamsa.ma/api/v1`
- **Format:** Strict JSON (`Accept: application/json`, `Content-Type: application/json`)
- **Authentication:** HTTP Bearer Token via Laravel Sanctum

---

## 2. Authentication Flow (Sanctum Tokens)

### Step 1: Login or Register
Send credentials to the Auth module:
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "buyer@lamsa.ma",
  "password": "SecretPassword123!"
}
```

### Step 2: Store the Bearer Token
Successful login returns a `200 OK` with the plaintext personal access token:
```json
{
  "status": "success",
  "message": "Authenticated successfully",
  "data": {
    "user": {
      "id": 14,
      "name": "Yassine Alami",
      "email": "buyer@lamsa.ma",
      "role": "buyer"
    },
    "token": "3|K7d9Xm4P2..."
  }
}
```
Store this token in a secure client storage:
- **Web (Next.js):** Secure, `httpOnly` server cookie or secure session.
- **Mobile (React Native / Flutter):** Secure Keystore / Keychain (`flutter_secure_storage`).

### Step 3: Attach Token to Protected Requests
Pass the token in the `Authorization` header on all protected requests:
```http
GET /api/v1/buyer/orders
Authorization: Bearer 3|K7d9Xm4P2...
Accept: application/json
```

---

## 3. Real-Time WebSockets (Laravel Reverb)

Live auction bidding requires real-time bi-directional WebSocket communication.

### Step 1: Install Client Dependencies
```bash
npm install laravel-echo pusher-js
```

### Step 2: Initialize Laravel Echo
Configure Echo to connect to Lamsa's first-party Reverb server:

```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo;
  }
}

window.Pusher = Pusher;

export const echo = new Echo({
  broadcaster: 'reverb',
  key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
  wsHost: process.env.NEXT_PUBLIC_REVERB_HOST ?? '127.0.0.1',
  wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 8080),
  wssPort: 443,
  forceTLS: process.env.NODE_ENV === 'production',
  enabledTransports: ['ws', 'wss'],
  authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/api/v1/broadcasting/auth`,
  auth: {
    headers: {
      Authorization: `Bearer ${getUserToken()}`,
      Accept: 'application/json',
    },
  },
});
```

### Step 3: Listen for Live Auction Bids
Subscribe to the private auction channel to update the UI when bids occur:

```typescript
// Subscribe to private-auction.{auctionId}
echo.private(`auction.${auctionId}`)
  .listen('.bid.placed', (event: {
    auction_id: number;
    current_price: number;
    highest_bidder_id: number;
    bids_count: number;
  }) => {
    console.log('New bid placed!', event);
    // Update live UI state immediately without refreshing
    updateHighestBid(event.current_price);
    incrementBidCount(event.bids_count);
  });
```

---

## 4. Automated TypeScript Type Generation

Do not write API response types by hand! You can auto-generate TypeScript definitions directly from our Scramble OpenAPI endpoint:

```bash
# In your Next.js / frontend project root
npx openapi-typescript http://127.0.0.1:8000/docs/api.json -o types/api.d.ts
```

This generates exact TypeScript types for all models, request bodies, and responses:
```typescript
import { paths, components } from '@/types/api';

type Product = components['schemas']['ProductDetailResource'];
type CreateOrderPayload = paths['/api/v1/checkout']['post']['requestBody']['content']['application/json'];
```
Whenever the backend updates a field, re-running this command catches breaking changes during frontend build time!

---

## 5. Standard Error Envelope

All API errors return consistent HTTP status codes and structured JSON payloads:

### Validation Error (`422 Unprocessable Entity`)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Authentication Error (`401 Unauthorized`)
```json
{
  "message": "Unauthenticated."
}
```

### Authorization Error (`403 Forbidden`)
```json
{
  "message": "Artisan profile pending approval. You cannot access this action yet."
}
```

### Resource Not Found (`404 Not Found`)
```json
{
  "message": "Product not found or has been unpublished."
}
```

