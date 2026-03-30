<?php

namespace Database\Seeders;

use App\Enums\ArtisanStatus;
use App\Enums\ProductMode;
use App\Enums\UserRole;
use App\Models\Artisan;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $defaultPassword = env('ADMIN_PASSWORD') ?: Str::random(32);
        if (!env('ADMIN_PASSWORD')) {
            $this->command->warn("ADMIN_PASSWORD not set in .env. Generated random password: $defaultPassword");
        }

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@lamsa.ma'],
            [
                'name'              => 'Admin Lamsa',
                'password'          => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Buyer
        $buyer = User::updateOrCreate(
            ['email' => 'buyer@lamsa.ma'],
            [
                'name'              => 'Test Buyer',
                'password'          => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );
        $buyer->assignRole('buyer');

        // Artisan user + profile + store
        $artisanUser = User::updateOrCreate(
            ['email' => 'artisan@lamsa.ma'],
            [
                'name'              => 'Hassan Artisan',
                'password'          => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );
        $artisanUser->assignRole('artisan');

        $artisan = Artisan::updateOrCreate(
            ['user_id' => $artisanUser->id],
            [
                'bio'        => 'Traditional Moroccan craftsman from Fes.',
                'city'       => 'Fes',
                'region'     => 'Fes-Meknes',
                'status'     => ArtisanStatus::Active,
                'craft_type' => 'pottery',
            ]
        );

        $store = Store::updateOrCreate(
            ['artisan_id' => $artisan->id],
            [
                'name'        => 'Hassan Pottery',
                'slug'        => 'hassan-pottery',
                'description' => 'Handmade Moroccan pottery from Fes.',
                'is_active'   => true,
            ]
        );

        // Categories
        $categories = [
            ['name' => 'Pottery',  'slug' => 'pottery'],
            ['name' => 'Weaving',  'slug' => 'weaving'],
            ['name' => 'Leather',  'slug' => 'leather'],
            ['name' => 'Jewelry',  'slug' => 'jewelry'],
            ['name' => 'Woodwork', 'slug' => 'woodwork'],
        ];

        foreach ($categories as $cat) {
            Category::create([...$cat, 'is_active' => true]);
        }

        $potteryCategory = Category::where('slug', 'pottery')->first();

        // Products
        $productNames = [
            'Handmade Tagine',
            'Decorative Vase',
            'Ceramic Bowl Set',
            'Traditional Plate',
        ];

        foreach ($productNames as $name) {
            Product::create([
                'store_id'     => $store->id,
                'artisan_id'   => $artisan->id,
                'category_id'  => $potteryCategory->id,
                'name'         => $name,
                'slug'         => Str::slug($name) . '-' . Str::random(4),
                'description'  => "Beautiful handcrafted {$name} from Fes.",
                'price'        => rand(50, 300),
                'stock'        => rand(5, 20),
                'images'       => [],
                'mode'         => ProductMode::Direct,
                'is_published' => true,
            ]);
        }
    }
}
