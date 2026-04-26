<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $artisan = Auth::user()->artisan;

        if ($artisan?->store) {
            return redirect()
                ->route('artisan.store.show', $artisan->store)
                ->with('success', 'Your store is already set up.');
        }

        return view('artisan.store.create');
    }

    public function show(Store $store): View
    {
        $this->ensureOwnedByCurrentArtisan($store);

        return view('artisan.store.show', compact('store'));
    }

    public function edit(Store $store): View
    {
        $this->ensureOwnedByCurrentArtisan($store);

        return view('artisan.store.edit', compact('store'));
    }

    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $artisan = $request->user()->artisan;

        if ($artisan?->store) {
            return redirect()
                ->route('artisan.store.show', $artisan->store)
                ->with('success', 'Your store is already set up.');
        }

        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('stores/logos', 'public');
        }

        Store::create([
            'artisan_id' => $artisan->id,
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
            'logo' => $logoPath ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);


        return redirect()
            ->route('artisan.store.show', $artisan->store)
            ->with('success', 'Store created successfully.');
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $this->ensureOwnedByCurrentArtisan($store);

        $validated = $request->validated();
        $logoPath = null;
        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }

            $logoPath = $validated['logo']->store('stores/logos', 'public');
        }

        $store->update([
            'name' => $validated['name'] ?? $store->name,
            'slug' => isset($validated['name']) && $validated['name'] !== $store->name
                ? $this->generateUniqueSlug($validated['name'])
                : $store->slug,
            'description' => $validated['description'] ?? null,
            'logo' => $logoPath ?? $store->logo,
        ]);

        return redirect()
            ->route('artisan.store.show', $store)
            ->with('success', 'Store updated successfully.');
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Store::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function ensureOwnedByCurrentArtisan(Store $store): void
    {
        abort_unless(
            $store->artisan_id === Auth::user()->artisan?->id,
            403
        );
    }
}
