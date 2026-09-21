<?php

namespace Modules\Auction\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auction\Http\Requests\StoreAuctionRequest;
use Modules\Auction\Http\Requests\UpdateAuctionRequest;
use Modules\Auction\Http\Resources\AuctionResource;
use Modules\Auction\Models\Auction;
use Modules\Auction\Services\AuctionService;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use RuntimeException;

class ArtisanAuctionApiController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctionService,
    ) {}

    /**
     * Display a listing of auctions belonging to the authenticated artisan.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Artisan $artisan */
        $artisan = $user->artisan;

        $data = $this->auctionService->getArtisanAuctions($artisan);

        return response()->json([
            'data' => AuctionResource::collection($data['auctions']),
            'summary' => $data['summary'],
            'meta' => [
                'current_page' => $data['auctions']->currentPage(),
                'last_page' => $data['auctions']->lastPage(),
                'per_page' => $data['auctions']->perPage(),
                'total' => $data['auctions']->total(),
            ],
        ]);
    }

    /**
     * Create a new auction.
     */
    public function store(StoreAuctionRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Artisan $artisan */
        $artisan = $user->artisan;

        try {
            $auction = $this->auctionService->createAuction($artisan, $request->validated());

            return response()->json([
                'message' => 'Auction created successfully.',
                'data' => new AuctionResource($auction),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an existing auction.
     */
    public function update(UpdateAuctionRequest $request, Auction $auction): JsonResponse
    {
        $this->authorize('update', $auction);

        try {
            $updatedAuction = $this->auctionService->updateAuction($auction, $request->validated());

            return response()->json([
                'message' => 'Auction updated successfully.',
                'data' => new AuctionResource($updatedAuction),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel an active or scheduled auction.
     */
    public function cancel(Request $request, Auction $auction): JsonResponse
    {
        $this->authorize('cancel', $auction);

        try {
            $cancelledAuction = $this->auctionService->cancelAuction($auction);

            return response()->json([
                'message' => 'Auction cancelled successfully.',
                'data' => new AuctionResource($cancelledAuction),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
