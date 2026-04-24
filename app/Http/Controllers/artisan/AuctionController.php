<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\ArtisanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\UpdateAuctionRequest;
use App\Models\Auction;
use App\Models\Category;
use App\Services\AuctionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctionService,
    ) {
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', Auction::class);

        $store = Auth::user()->artisan?->store;

        if (! $store) {
            return redirect()
                ->route('artisan.store.create')
                ->with('success', 'Create your store before launching auctions.');
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('artisan.auctions.create', compact('categories'));
    }

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $artisan = $user->artisan;

        if (! $artisan && ! $user->hasRole('artisan')) {
            return redirect()->route('home');
        }

        if (! $artisan || $artisan->status === ArtisanStatus::Pending) {
            return view('artisan.pending', [
                'status' => ArtisanStatus::Pending,
            ]);
        }

        if ($artisan->status !== ArtisanStatus::Active) {
            return view('artisan.pending', [
                'status' => $artisan->status,
            ]);
        }

        return view('artisan.auctions.index', $this->auctionService->getArtisanIndexData($artisan));
    }

    public function store(StoreAuctionRequest $request): RedirectResponse
    {
        $this->authorize('create', Auction::class);

        $artisan = Auth::user()->artisan;
        $validated = $request->validated();

        if (! $artisan?->store) {
            return redirect()
                ->route('artisan.store.create')
                ->with('error', 'Create your store before launching auctions.');
        }

        $this->auctionService->createAuction($artisan, $validated, $request);

        return redirect()
            ->route('artisan.auctions')
            ->with('success', 'Auction created successfully.');
    }

    public function edit(Auction $auction): View|RedirectResponse
    {
        $this->authorize('update', $auction);

        if ($message = $this->auctionService->editRestrictionMessage($auction)) {
            return redirect()
                ->route('artisan.auctions')
                ->with('error', $message);
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('artisan.auctions.edit', compact('auction', 'categories'));
    }

    public function update(UpdateAuctionRequest $request, Auction $auction): RedirectResponse
    {
        $this->authorize('update', $auction);

        if ($message = $this->auctionService->editRestrictionMessage($auction)) {
            return redirect()
                ->route('artisan.auctions')
                ->with('error', $message);
        }

        $this->auctionService->updateAuction($auction, $request->validated(), $request);

        return redirect()
            ->route('artisan.auctions')
            ->with('success', 'Auction updated successfully.');
    }

    public function togglePublish(Auction $auction): RedirectResponse
    {
        $this->authorize('update', $auction);

        $isPublished = $this->auctionService->togglePublish($auction);

        return back()->with('success', $isPublished
            ? 'Auction published successfully.'
            : 'Auction unpublished successfully.');
    }

    public function cancel(Auction $auction): RedirectResponse
    {
        $this->authorize('update', $auction);

        if (! $this->auctionService->cancelAuction($auction)) {
            return back()->with('error', 'Only active auctions can be cancelled.');
        }

        return back()->with('success', 'Auction cancelled successfully.');
    }

    public function destroy(Auction $auction): RedirectResponse
    {
        $this->authorize('delete', $auction);

        if (! $this->auctionService->deleteAuction($auction)) {
            return back()->with('error', 'You cannot delete an auction that already has bids.');
        }

        return redirect()
            ->route('artisan.auctions')
            ->with('success', 'Auction deleted successfully.');
    }
}
