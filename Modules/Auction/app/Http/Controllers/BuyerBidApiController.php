<?php

namespace Modules\Auction\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auction\Http\Requests\PlaceBidRequest;
use Modules\Auction\Http\Resources\BidResource;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auction\Services\BidService;
use Modules\Auth\Models\User;
use RuntimeException;

class BuyerBidApiController extends Controller
{
    public function __construct(
        private readonly BidService $bidService,
    ) {}

    /**
     * Place a bid on an active auction.
     */
    public function store(PlaceBidRequest $request, Auction $auction): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $bid = $this->bidService->placeBid(
                user: $user,
                auctionId: $auction->id,
                amount: (float) $request->validated('amount'),
            );

            return response()->json([
                'message' => 'Bid placed successfully.',
                'data' => new BidResource($bid),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * View all bids placed by the authenticated buyer.
     */
    public function myBids(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $bids = Bid::query()
            ->where('user_id', $user->id)
            ->with(['auction.store', 'auction.category', 'auction.highestBid'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => BidResource::collection($bids),
            'meta' => [
                'current_page' => $bids->currentPage(),
                'last_page' => $bids->lastPage(),
                'per_page' => $bids->perPage(),
                'total' => $bids->total(),
            ],
        ]);
    }

    /**
     * Withdraw a bid from an active auction.
     */
    public function destroy(Request $request, Bid $bid): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->bidService->withdrawBid($user, $bid);

            return response()->json([
                'message' => 'Bid withdrawn successfully.',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
