<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\UpdateAuctionRequest;
use App\Models\Artisan;
use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuctionService
{
    public function getArtisanIndexData(Artisan $artisan): array
    {
        $this->syncExpiredAuctions($artisan->id);

        $auctions = $artisan->auctions()
            ->with(['store', 'category', 'highestBid.user'])
            ->withCount('bids')
            ->latest('starts_at')
            ->paginate(10);

        $summary = [
            'liveAuctions' => $artisan->auctions()
                ->where('status', AuctionStatus::Active)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>', now())
                ->count(),
            'scheduledAuctions' => $artisan->auctions()
                ->where('status', AuctionStatus::Active)
                ->where('starts_at', '>', now())
                ->count(),
            'endedAuctions' => $artisan->auctions()
                ->where('status', AuctionStatus::Ended)
                ->count(),
            'cancelledAuctions' => $artisan->auctions()
                ->where('status', AuctionStatus::Cancelled)
                ->count(),
            'totalBids' => Bid::query()
                ->whereHas('auction', function ($query) use ($artisan) {
                    $query->where('artisan_id', $artisan->id);
                })
                ->count(),
        ];

        return compact('auctions', 'summary');
    }

    public function createAuction(Artisan $artisan, array $validated, StoreAuctionRequest $request): Auction
    {
        return Auction::create([
            'store_id' => $artisan->store->id,
            'artisan_id' => $artisan->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'images' => $this->storeUploadedImages($request),
            'starting_price' => $validated['starting_price'],
            'reserve_price' => $validated['reserve_price'] ?? null,
            'current_price' => $validated['starting_price'],
            'status' => AuctionStatus::Active,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_published' => $validated['is_published'] ?? false,
        ]);
    }

    public function updateAuction(Auction $auction, array $validated, UpdateAuctionRequest $request): void
    {
        $imagePaths = $auction->images ?? [];

        if ($request->hasFile('images')) {
            $this->deleteStoredImages($auction->images ?? []);
            $imagePaths = $this->storeUploadedImages($request);
        }

        $auction->update([
            'category_id' => $validated['category_id'] ?? $auction->category_id,
            'name' => $validated['name'] ?? $auction->name,
            'slug' => isset($validated['name'])
                ? $this->generateUniqueSlug($validated['name'], $auction->id)
                : $auction->slug,
            'description' => $validated['description'] ?? $auction->description,
            'images' => $imagePaths,
            'starting_price' => $validated['starting_price'] ?? $auction->starting_price,
            'reserve_price' => array_key_exists('reserve_price', $validated)
                ? $validated['reserve_price']
                : $auction->reserve_price,
            'current_price' => $auction->bids()->exists()
                ? $auction->current_price
                : ($validated['starting_price'] ?? $auction->starting_price),
            'starts_at' => $validated['starts_at'] ?? $auction->starts_at,
            'ends_at' => $validated['ends_at'] ?? $auction->ends_at,
            'is_published' => $validated['is_published'] ?? false,
        ]);
    }

    public function togglePublish(Auction $auction): bool
    {
        $auction->update([
            'is_published' => ! $auction->is_published,
        ]);

        return $auction->is_published;
    }

    public function cancelAuction(Auction $auction): bool
    {
        if ($auction->status !== AuctionStatus::Active) {
            return false;
        }

        $auction->update([
            'status' => AuctionStatus::Cancelled,
            'is_published' => false,
        ]);

        return true;
    }

    public function deleteAuction(Auction $auction): bool
    {
        if ($auction->bids()->exists()) {
            return false;
        }

        $this->deleteStoredImages($auction->images ?? []);
        $auction->delete();

        return true;
    }

    public function editRestrictionMessage(Auction $auction): ?string
    {
        if ($auction->bids()->exists()) {
            return 'Auctions with bids can no longer be edited.';
        }

        if ($auction->starts_at?->isPast()) {
            return 'Auctions that already started can no longer be edited.';
        }

        return null;
    }

    public function syncExpiredAuctions(int $artisanId): void
    {
        Auction::query()
            ->where('artisan_id', $artisanId)
            ->where('status', AuctionStatus::Active)
            ->where('ends_at', '<=', now())
            ->update([
                'status' => AuctionStatus::Ended,
                'is_published' => false,
            ]);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Auction::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function storeUploadedImages(StoreAuctionRequest|UpdateAuctionRequest $request): array
    {
        return collect($request->file('images', []))
            ->map(fn ($image) => $image->store('auctions/images', 'public'))
            ->all();
    }

    private function deleteStoredImages(array $images): void
    {
        foreach ($images as $image) {
            if (is_string($image) && $image !== '') {
                Storage::disk('public')->delete($image);
            }
        }
    }
}
