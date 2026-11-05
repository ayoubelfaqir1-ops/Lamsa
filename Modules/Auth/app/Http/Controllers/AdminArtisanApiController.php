<?php

namespace Modules\Auth\Http\Controllers;

use App\Enums\ArtisanStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Events\ArtisanApproved;
use Modules\Auth\Events\ArtisanRejected;
use Modules\Auth\Http\Requests\AdminUpdateArtisanStatusRequest;
use Modules\Auth\Http\Resources\AdminArtisanResource;
use Modules\Auth\Models\Artisan;

class AdminArtisanApiController extends Controller
{
    /**
     * List all artisans with status filtering, search, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $query = Artisan::query()->with(['user', 'store']);

        if ($request->filled('status')) {
            $statusInput = (string) $request->input('status');
            $statusValue = $statusInput === 'approved' ? ArtisanStatus::Active->value : $statusInput;
            $query->where('status', $statusValue);
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('craft_type', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $artisans = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => AdminArtisanResource::collection($artisans->items()),
            'meta' => [
                'current_page' => $artisans->currentPage(),
                'last_page' => $artisans->lastPage(),
                'per_page' => $artisans->perPage(),
                'total' => $artisans->total(),
            ],
        ]);
    }

    /**
     * Update the approval status of an artisan.
     */
    public function updateStatus(AdminUpdateArtisanStatusRequest $request, Artisan $artisan): JsonResponse
    {
        $statusInput = (string) $request->validated('status');

        if ($statusInput === 'approved' || $statusInput === ArtisanStatus::Active->value) {
            $artisan->update(['status' => ArtisanStatus::Active]);
            ArtisanApproved::dispatch($artisan->fresh(['user', 'store']));
            $message = 'Artisan has been approved successfully.';
        } elseif ($statusInput === ArtisanStatus::Rejected->value) {
            $artisan->update(['status' => ArtisanStatus::Rejected]);
            ArtisanRejected::dispatch($artisan->fresh(['user', 'store']), $request->validated('reason'));
            $message = 'Artisan application has been rejected.';
        } else {
            $newStatus = ArtisanStatus::from($statusInput);
            $artisan->update(['status' => $newStatus]);
            $message = "Artisan status updated to {$newStatus->value}.";
        }

        return response()->json([
            'message' => $message,
            'data' => new AdminArtisanResource($artisan->fresh(['user', 'store'])),
        ]);
    }
}
