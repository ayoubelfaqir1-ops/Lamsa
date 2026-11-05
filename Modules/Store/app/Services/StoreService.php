<?php

namespace Modules\Store\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Auth\Models\Artisan;
use Modules\Store\Models\Store;
use RuntimeException;

class StoreService
{
    /**
     * Create a new store for the given artisan.
     *
     * @param  array{name: string, description?: string|null}  $data
     */
    public function createStore(Artisan $artisan, array $data, ?UploadedFile $logo = null): Store
    {
        if ($artisan->store()->exists()) {
            throw new RuntimeException('Artisan already has an established store.');
        }

        $slug = $this->generateUniqueSlug($data['name']);
        $logoPath = null;

        if ($logo) {
            $logoPath = $logo->store('stores/logos', 'public');
        }

        /** @var Store $store */
        $store = Store::create([
            'artisan_id' => $artisan->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'logo' => $logoPath,
            'is_active' => true,
        ]);

        return $store;
    }

    /**
     * Update an existing store.
     *
     * @param  array{name?: string, description?: string|null, is_active?: bool}  $data
     */
    public function updateStore(Store $store, array $data, ?UploadedFile $logo = null): Store
    {
        $attributes = [];

        if (isset($data['name'])) {
            $attributes['name'] = $data['name'];
            if ($data['name'] !== $store->name) {
                $attributes['slug'] = $this->generateUniqueSlug($data['name'], $store->id);
            }
        }

        if (array_key_exists('description', $data)) {
            $attributes['description'] = $data['description'];
        }

        if (array_key_exists('is_active', $data)) {
            $attributes['is_active'] = (bool) $data['is_active'];
        }

        if ($logo) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $attributes['logo'] = $logo->store('stores/logos', 'public');
        }

        $store->update($attributes);

        return $store->fresh();
    }

    /**
     * Remove the store logo file from storage and clear the attribute.
     */
    public function deleteLogo(Store $store): Store
    {
        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
            $store->update(['logo' => null]);
        }

        return $store->fresh();
    }

    /**
     * Generate a unique slug for a store name.
     */
    public function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($name);

        if (empty($baseSlug)) {
            $baseSlug = 'store-'.Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Store::query()
            ->where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

