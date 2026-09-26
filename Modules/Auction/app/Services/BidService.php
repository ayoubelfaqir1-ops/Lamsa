<?php

namespace Modules\Auction\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Events\BidPlaced;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auth\Models\User;
use RuntimeException;

class BidService
{
    /**
     * Place a bid on an active auction with pessimistic concurrency locking.
     */
    public function placeBid(User $user, int $auctionId, float $amount): Bid
    {
        return DB::transaction(function () use ($user, $auctionId, $amount) {
            /** @var Auction|null $auction */
            $auction = Auction::query()
                ->lockForUpdate()
                ->find($auctionId);

            if (! $auction) {
                throw new RuntimeException('Auction not found.');
            }

            // 1. Artisan self-bidding check (anti-shill rule)
            if ($auction->artisan->user_id === $user->id) {
                throw new RuntimeException('You cannot bid on your own auction.');
            }

            // 2. Lifecycle and time window validation
            if ($auction->status !== AuctionStatus::Active) {
                throw new RuntimeException('This auction is not active.');
            }

            if ($auction->starts_at->isFuture()) {
                throw new RuntimeException('This auction has not started yet.');
            }

            if ($auction->ends_at->isPast()) {
                throw new RuntimeException('This auction has already ended.');
            }

            // 3. Price validation against TRUE live database state
            $currentThreshold = max(
                (float) $auction->current_price,
                (float) $auction->starting_price
            );

            // If there are existing bids, bid must strictly exceed current_price.
            // If there are no bids, bid must be at least starting_price (or exceed it if current_price is already set).
            $hasExistingBids = $auction->bids()->exists();
            if ($hasExistingBids && $amount <= (float) $auction->current_price) {
                throw new RuntimeException("Your bid must strictly exceed the current price of {$auction->current_price}.");
            }

            if (! $hasExistingBids && $amount < (float) $auction->starting_price) {
                throw new RuntimeException("Your bid must be at least the starting price of {$auction->starting_price}.");
            }

            // 4. Persist the bid and update the auction's current_price
            /** @var Bid $bid */
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $amount,
            ]);

            $auction->update([
                'current_price' => $amount,
            ]);

            $bid->setRelation('user', $user);
            $bid->setRelation('auction', $auction);

            // 5. Broadcast real-time event to private-auction.{id} after transaction commit
            BidPlaced::dispatch($bid);

            return $bid;
        });
    }

    /**
     * Withdraw a bid if permitted.
     */
    public function withdrawBid(User $user, Bid $bid): void
    {
        DB::transaction(function () use ($user, $bid) {
            if ($bid->user_id !== $user->id) {
                throw new RuntimeException('You can only withdraw your own bids.');
            }

            /** @var Auction|null $auction */
            $auction = Auction::query()
                ->lockForUpdate()
                ->find($bid->auction_id);

            /** @var Bid|null $lockedBid */
            $lockedBid = Bid::query()
                ->lockForUpdate()
                ->find($bid->id);

            if (! $auction || ! $lockedBid) {
                throw new RuntimeException('Bid or auction no longer exists.');
            }

            if ($auction->status !== AuctionStatus::Active || $auction->ends_at->isPast()) {
                throw new RuntimeException('You cannot withdraw bids from ended auctions.');
            }

            $lockedBid->delete();

            // Re-calculate the highest remaining bid
            $highestRemainingBid = $auction->bids()->max('amount');

            $auction->update([
                'current_price' => $highestRemainingBid ?? $auction->starting_price,
            ]);
        });
    }
}
