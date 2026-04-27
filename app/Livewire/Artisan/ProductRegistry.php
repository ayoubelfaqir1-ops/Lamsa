<?php

namespace App\Livewire\Artisan;

use App\Models\Product;
use App\Enums\ProductStatus;
use Illuminate\Support\Facades\Cache;
use livewire\Component;
use Livewire\WithPagination;

class ProductRegistry extends Component
{
    public $status = 'all';
    public $search = '';
    public function render()
    {
        $query = Product::with('store','artisan');

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        $products = $query->latest()->paginate(15);

        $summary = Cache::remember('admin.artisans.summary', now()->addMinutes(5), function () {
            return [
                'pendingCount' => Product::where('status', ProductStatus::Pending)->count(),
                'activeCount' => Product::where('status', ProductStatus::Active)->count(),
            ];
        });

        return view('livewire.artisan.products', [
            'artisans' => $artisans,
            'pendingCount' => $summary['pendingCount'],
            'activeCount' => $summary['activeCount'],
        ]);
    }
};