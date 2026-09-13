<?php

namespace App\Http\Controllers\Buyer;

use App\Enums\AuctionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceBidRequest;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BidController extends Controller
{
    public function store(PlaceBidRequest $request, Auction $auction): RedirectResponse
    {
        $this->authorize('bid', $auction);

        DB::transaction(function () use ($request, $auction) {
            $lockedAuction = Auction::query()->lockForUpdate()->findOrFail($auction->id);

            if (
                $lockedAuction->status !== AuctionStatus::Active
                || $lockedAuction->starts_at->isFuture()
                || $lockedAuction->ends_at->isPast()
            ) {
                abort(422, 'This auction is no longer active.');
            }

            $minimumAmount = max(
                (float) $lockedAuction->current_price,
                (float) $lockedAuction->starting_price
            );

            if ((float) $request->amount <= $minimumAmount) {
                abort(422, 'Your bid must be higher than the current price.');
            }

            Bid::create([
                'auction_id' => $lockedAuction->id,
                'user_id' => Auth::id(),
                'amount' => $request->amount,
            ]);

            $lockedAuction->update([
                'current_price' => $request->amount,
            ]);
        });

        return back()->with('success', 'Your bid has been placed successfully.');
    }

    public function myBids(): View
    {
        $bids = Bid::query()
            ->with(['auction.store', 'auction.category'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('bids.my-bids', compact('bids'));
    }

    public function destroy(Bid $bid): RedirectResponse
    {
        if ($bid->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($bid) {
            $lockedAuction = Auction::query()->lockForUpdate()->findOrFail($bid->auction_id);
            $lockedBid = Bid::query()->lockForUpdate()->findOrFail($bid->id);

            if (
                $lockedAuction->status !== AuctionStatus::Active
                || $lockedAuction->ends_at->isPast()
            ) {
                abort(422, 'You cannot withdraw this bid.');
            }

            $lockedBid->delete();

            $highestRemainingBid = $lockedAuction->bids()->max('amount');

            $lockedAuction->update([
                'current_price' => $highestRemainingBid ?? $lockedAuction->starting_price,
            ]);
        });

        return back()->with('success', 'Your bid has been withdrawn.');
    }
}
