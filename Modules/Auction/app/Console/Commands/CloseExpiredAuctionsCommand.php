<?php

namespace Modules\Auction\Console\Commands;

use Illuminate\Console\Command;
use Modules\Auction\Services\AuctionService;

class CloseExpiredAuctionsCommand extends Command
{
    protected $signature = 'auctions:close-expired';

    protected $description = 'Close expired auctions, determine winners, and broadcast results';

    public function handle(AuctionService $auctionService): int
    {
        $this->info('Sweeping expired auctions...');

        $closed = $auctionService->closeExpired();

        $this->info("Successfully closed {$closed->count()} auction(s).");

        return Command::SUCCESS;
    }
}
