<?php

namespace Modules\Auction\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auction\Http\Resources\AuctionResource;
use Modules\Auction\Models\Auction;
use Modules\Auction\Services\AuctionService;

class PublicAuctionApiController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctionService,
    ) {}

    /**
     * Display a paginated list of public auctions with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 12), 50);

        $auctions = $this->auctionService->getPublicAuctions(
            filters: $request->only(['status', 'category', 'search', 'sort']),
            perPage: $perPage > 0 ? $perPage : 12,
        );

        return response()->json([
            'data' => AuctionResource::collection($auctions),
            'meta' => [
                'current_page' => $auctions->currentPage(),
                'last_page' => $auctions->lastPage(),
                'per_page' => $auctions->perPage(),
                'total' => $auctions->total(),
            ],
        ]);
    }

    /**
     * Display a single auction by slug or ID with its bid history.
     */
    public function show(string $idOrSlug): JsonResponse
    {
        /** @var Auction $auction */
        $auction = is_numeric($idOrSlug)
            ? Auction::query()->where('id', (int) $idOrSlug)->firstOrFail()
            : Auction::query()->where('slug', $idOrSlug)->firstOrFail();

        $auction->load([
            'store',
            'category',
            'artisan.user',
            'bids.user',
            'highestBid.user',
            'winner',
        ]);

        return response()->json([
            'data' => new AuctionResource($auction),
        ]);
    }
}
