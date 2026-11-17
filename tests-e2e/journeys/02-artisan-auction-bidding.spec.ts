import { test, expect } from '@playwright/test';
import { loginBuyer, loginArtisan } from '../helpers/api-client';

test.describe('E2E Journey 2: Live Auction Bidding & Price Escalation', () => {
  test('Complete bidding flow: find auction ➔ place higher bid ➔ verify updated price & history', async ({ request }) => {
    // 1. Authenticate Buyer
    const buyer = await loginBuyer(request);
    expect(buyer.token).toBeTruthy();

    // 2. Discover Public Auctions
    const auctionsRes = await request.get('/api/v1/auctions');
    expect(auctionsRes.ok()).toBeTruthy();
    const auctionsJson = await auctionsRes.json();
    const auctions = auctionsJson.data || auctionsJson;
    expect(auctions.length).toBeGreaterThan(0);

    // Pick an active auction
    const activeAuction = auctions.find((a: any) => a.status === 'active' || !a.is_ended);
    expect(activeAuction, 'At least one active auction must exist').toBeDefined();

    const initialPrice = Number(activeAuction.current_price || activeAuction.starting_price || 100);
    const newBidAmount = initialPrice + 25;

    // 3. Buyer places a higher bid
    const bidRes = await request.post(`/api/v1/auctions/${activeAuction.id}/bids`, {
      headers: buyer.headers,
      data: {
        amount: newBidAmount,
      },
    });

    // In Lamsa, bidding returns 201 or 200 with the new bid data
    expect([200, 201]).toContain(bidRes.status());
    const bidJson = await bidRes.json();
    expect(bidJson).toBeDefined();

    // 4. Verify the auction's current_price has escalated on the public endpoint
    const updatedAuctionRes = await request.get(`/api/v1/auctions/${activeAuction.id}`);
    expect(updatedAuctionRes.status()).toBe(200);
    const updatedJson = await updatedAuctionRes.json();
    const auctionData = updatedJson.data || updatedJson;
    expect(Number(auctionData.current_price)).toBe(newBidAmount);

    // 5. Verify the bid appears in the buyer's private bid history
    const myBidsRes = await request.get('/api/v1/buyer/bids', { headers: buyer.headers });
    expect(myBidsRes.status()).toBe(200);
    const myBidsJson = await myBidsRes.json();
    const bidsList = myBidsJson.data || myBidsJson;
    const recordedBid = bidsList.find((b: any) => b.auction_id === activeAuction.id && Number(b.amount) === newBidAmount);
    expect(recordedBid).toBeDefined();
  });
});

