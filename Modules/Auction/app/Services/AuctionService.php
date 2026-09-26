<?php

namespace Modules\Auction\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Events\AuctionClosed;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auth\Models\Artisan;
use RuntimeException;

class AuctionService
{
    /**
     * Create an auction for an approved artisan.
     *
     * @param  array{category_id: int, name: string, description?: string|null, starting_price: float, reserve_price?: float|null, starts_at: string, ends_at: string, images?: array<string>|null}  $validated
     */
    public function createAuction(Artisan $artisan, array $validated): Auction
    {
        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = Carbon::parse($validated['ends_at']);

        if ($endsAt->lte($startsAt)) {
            throw new RuntimeException('The auction end time must be after the start time.');
        }

        // Determine status: scheduled if start time is in future, active otherwise
        $status = $startsAt->isFuture() ? AuctionStatus::Scheduled : AuctionStatus::Active;
        $startingPrice = (float) $validated['starting_price'];

        $store = $artisan->store;
        if (! $store) {
            throw new RuntimeException('Artisan must have a store to create an auction.');
        }

        $auction = Auction::create([
            'store_id' => $store->id,
            'artisan_id' => $artisan->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'images' => $validated['images'] ?? [],
            'starting_price' => $startingPrice,
            'current_price' => $startingPrice,
            'reserve_price' => isset($validated['reserve_price']) ? (float) $validated['reserve_price'] : null,
            'status' => $status,
            'is_published' => true,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return $auction->load(['store', 'category', 'artisan.user']);
    }

    /**
     * Update an auction if no bids exist and it hasn't started.
     *
     * @param  array{category_id?: int, name?: string, description?: string|null, starting_price?: float, reserve_price?: float|null, starts_at?: string, ends_at?: string, images?: array<string>|null}  $validated
     */
    public function updateAuction(Auction $auction, array $validated): Auction
    {
        if ($auction->bids()->exists()) {
            throw new RuntimeException('Auctions with bids cannot be edited.');
        }

        if ($auction->starts_at->isPast()) {
            throw new RuntimeException('Auctions that have already started cannot be edited.');
        }

        if (isset($validated['name']) && $validated['name'] !== $auction->name) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $auction->id);
        }

        if (isset($validated['starts_at']) || isset($validated['ends_at'])) {
            $startsAt = isset($validated['starts_at']) ? Carbon::parse($validated['starts_at']) : $auction->starts_at;
            $endsAt = isset($validated['ends_at']) ? Carbon::parse($validated['ends_at']) : $auction->ends_at;

            if ($endsAt->lte($startsAt)) {
                throw new RuntimeException('The auction end time must be after the start time.');
            }

            $validated['status'] = $startsAt->isFuture() ? AuctionStatus::Scheduled : AuctionStatus::Active;
        }

        if (isset($validated['starting_price'])) {
            $validated['current_price'] = (float) $validated['starting_price'];
        }

        $auction->update($validated);

        return $auction->fresh(['store', 'category', 'artisan.user']);
    }

    /**
     * Cancel an active or scheduled auction.
     */
    public function cancelAuction(Auction $auction): Auction
    {
        if ($auction->status === AuctionStatus::Ended || $auction->status === AuctionStatus::Cancelled) {
            throw new RuntimeException('This auction cannot be cancelled.');
        }

        $auction->update([
            'status' => AuctionStatus::Cancelled,
            'is_published' => false,
        ]);

        return $auction;
    }

    /**
     * Close expired auctions idempotently.
     *
     * Sweeps active auctions past their ends_at, determines the winner respecting reserve_price,
     * updates status to ended, and broadcasts AuctionClosed.
     *
     * @return Collection<int, Auction>
     */
    public function closeExpired(): Collection
    {
        // 1. First, automatically activate any scheduled auctions whose starts_at has arrived
        Auction::query()
            ->where('status', AuctionStatus::Scheduled)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->update(['status' => AuctionStatus::Active]);

        // 2. Fetch active auctions past their end time
        $expiredAuctions = Auction::query()
            ->where('status', AuctionStatus::Active)
            ->where('ends_at', '<=', now())
            ->get();

        $closedAuctions = collect();

        foreach ($expiredAuctions as $auction) {
            $closed = $this->closeSingleAuction($auction->id);
            if ($closed) {
                $closedAuctions->push($closed);
            }
        }

        return $closedAuctions;
    }

    /**
     * Close a single auction atomically and idempotently.
     */
    public function closeSingleAuction(int $auctionId): ?Auction
    {
        return DB::transaction(function () use ($auctionId) {
            /** @var Auction|null $auction */
            $auction = Auction::query()
                ->lockForUpdate()
                ->find($auctionId);

            // Idempotency check: if not found, or not active, or not expired yet, skip
            if (! $auction || $auction->status !== AuctionStatus::Active || $auction->ends_at->isFuture()) {
                return null;
            }

            // Determine highest bid
            /** @var Bid|null $highestBid */
            $highestBid = $auction->bids()->orderByDesc('amount')->first();

            $winnerId = null;
            $winningBidId = null;

            if ($highestBid !== null) {
                // Reserve price evaluation
                $reserveMet = $auction->reserve_price === null
                    || (float) $highestBid->amount >= (float) $auction->reserve_price;

                if ($reserveMet) {
                    $winnerId = $highestBid->user_id;
                    $winningBidId = $highestBid->id;
                }
            }

            $auction->update([
                'status' => AuctionStatus::Ended,
                'is_published' => false,
                'winner_id' => $winnerId,
                'winning_bid_id' => $winningBidId,
            ]);

            $auction->load(['winner', 'winningBid', 'highestBid']);

            // Broadcast after successful commit
            AuctionClosed::dispatch($auction);

            return $auction;
        });
    }

    /**
     * Paginated public catalog with filters.
     *
     * @param  array{category?: string|null, status?: string|null, search?: string|null, sort?: string|null}  $filters
     */
    public function getPublicAuctions(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        return Auction::query()
            ->with(['store', 'category', 'highestBid.user'])
            ->withCount('bids')
            ->published()
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                if ($status === 'live') {
                    $query->live();
                } elseif ($status === 'scheduled') {
                    $query->where('status', AuctionStatus::Scheduled);
                } elseif ($status === 'ended') {
                    $query->where('status', AuctionStatus::Ended);
                }
            }, function (Builder $query) {
                // Default: show live and scheduled auctions
                $query->whereIn('status', [AuctionStatus::Active, AuctionStatus::Scheduled]);
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $category) {
                $query->whereHas('category', fn (Builder $q) => $q->where('slug', $category));
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['sort'] ?? null, function (Builder $query, string $sort) {
                match ($sort) {
                    'ending_soon' => $query->orderBy('ends_at', 'asc'),
                    'price_asc' => $query->orderBy('current_price', 'asc'),
                    'price_desc' => $query->orderBy('current_price', 'desc'),
                    'bids_count' => $query->orderByDesc('bids_count'),
                    default => $query->latest('starts_at'),
                };
            }, function (Builder $query) {
                $query->orderBy('ends_at', 'asc');
            })
            ->paginate($perPage);
    }

    /**
     * Get artisan auctions summary & paginated list.
     */
    public function getArtisanAuctions(Artisan $artisan, int $perPage = 10): array
    {
        $perPage = max(1, min(100, $perPage));

        $auctions = Auction::query()
            ->where('artisan_id', $artisan->id)
            ->with(['store', 'category', 'highestBid.user', 'winner'])
            ->withCount('bids')
            ->latest('starts_at')
            ->paginate($perPage);

        $summary = [
            'liveAuctions' => Auction::query()
                ->where('artisan_id', $artisan->id)
                ->where('status', AuctionStatus::Active)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>', now())
                ->count(),
            'scheduledAuctions' => Auction::query()
                ->where('artisan_id', $artisan->id)
                ->where('status', AuctionStatus::Scheduled)
                ->count(),
            'endedAuctions' => Auction::query()
                ->where('artisan_id', $artisan->id)
                ->where('status', AuctionStatus::Ended)
                ->count(),
            'cancelledAuctions' => Auction::query()
                ->where('artisan_id', $artisan->id)
                ->where('status', AuctionStatus::Cancelled)
                ->count(),
            'totalBids' => Bid::query()
                ->whereHas('auction', fn ($q) => $q->where('artisan_id', $artisan->id))
                ->count(),
        ];

        return compact('auctions', 'summary');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Auction::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
