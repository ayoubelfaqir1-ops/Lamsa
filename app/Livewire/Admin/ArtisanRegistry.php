<?php

namespace App\Livewire\Admin;

use App\Models\Artisan;
use App\Enums\ArtisanStatus;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class ArtisanRegistry extends Component
{
    use WithPagination;

    public $status = 'all';
    public $search = '';

    protected $queryString = [
        'status' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updateStatus($artisanId, $status)
    {
        $artisan = Artisan::findOrFail($artisanId);
        $artisan->update(['status' => $status]);
        $this->clearSummaryCache();

        session()->flash('success', "Artisan status updated to {$status} successfully.");
    }

    public function render()
    {
        $query = Artisan::with('user', 'store');

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $artisans = $query->latest()->paginate(15);
        
        $summary = Cache::remember('admin.artisans.summary', now()->addMinutes(5), function () {
            return [
                'pendingCount' => Artisan::where('status', ArtisanStatus::Pending)->count(),
                'activeCount' => Artisan::where('status', ArtisanStatus::Active)->count(),
            ];
        });

        return view('livewire.admin.artisans-registry', [
            'artisans' => $artisans,
            'pendingCount' => $summary['pendingCount'],
            'activeCount' => $summary['activeCount'],
        ]);
    }

    private function clearSummaryCache(): void
    {
        Cache::forget('admin.artisans.summary');
    }
}
