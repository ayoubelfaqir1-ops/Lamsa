<?php

namespace Database\Seeders;

use App\Enums\ArtisanStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Artisan;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $defaultPassword = env('ADMIN_PASSWORD') ?: Str::random(32);
        if (!env('ADMIN_PASSWORD')) {
            $this->command->warn("ADMIN_PASSWORD not set in .env. Generated random password: $defaultPassword");
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@lamsa.ma'],
            [
                'name' => 'Admin Lamsa',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $buyers = $this->seedBuyers($defaultPassword);
        $categories = $this->seedCategories();
        $products = $this->seedArtisansStoresAndProducts($defaultPassword, $categories);

        $this->seedReviews($buyers, $products);
        $this->seedOrders($buyers, $products);
        $this->seedAuctions($buyers, $categories);
    }

    private function seedBuyers(string $defaultPassword): array
    {
        $buyers = [];

        $buyerDefinitions = [
            ['email' => 'buyer@lamsa.ma', 'name' => 'Test Buyer'],
            ['email' => 'sara.buyer@lamsa.ma', 'name' => 'Sara Bennett'],
            ['email' => 'youssef.collector@lamsa.ma', 'name' => 'Youssef Collector'],
            ['email' => 'amina.home@lamsa.ma', 'name' => 'Amina Home'],
            ['email' => 'leo.curator@lamsa.ma', 'name' => 'Leo Curator'],
        ];

        foreach ($buyerDefinitions as $definition) {
            $buyer = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                ]
            );
            $buyer->assignRole('buyer');
            $buyers[$definition['email']] = $buyer;
        }

        return $buyers;
    }

    private function seedCategories(): array
    {
        $categoryDefinitions = [
            ['name' => 'Pottery', 'slug' => 'pottery'],
            ['name' => 'Ceramics', 'slug' => 'ceramics'],
            ['name' => 'Weaving', 'slug' => 'weaving'],
            ['name' => 'Leather', 'slug' => 'leather'],
            ['name' => 'Jewelry', 'slug' => 'jewelry'],
            ['name' => 'Woodwork', 'slug' => 'woodwork'],
        ];

        $categories = [];

        foreach ($categoryDefinitions as $definition) {
            $category = Category::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'is_active' => true,
                ]
            );

            $categories[$definition['slug']] = $category;
        }

        return $categories;
    }

    private function seedArtisansStoresAndProducts(string $defaultPassword, array $categories): array
    {
        $artisans = [
            [
                'user' => [
                    'email' => 'artisan@lamsa.ma',
                    'name' => 'Hassan Artisan',
                ],
                'artisan' => [
                    'bio' => "Hassan learned pottery in Fes through a family workshop where everyday objects were treated with the same care as ceremonial pieces.\nHe works with warm glazes, steady forms, and practical objects meant to be used, not only displayed.",
                    'city' => 'Fes',
                    'region' => 'Fes-Meknes',
                    'craft_type' => 'pottery',
                ],
                'store' => [
                    'name' => 'Hassan Pottery',
                    'slug' => 'hassan-pottery',
                    'description' => 'Handmade pottery and ceramics from Fes, shaped for real kitchens, long meals, and warm homes.',
                    'logo' => 'https://picsum.photos/id/64/400/400',
                ],
                'products' => [
                    [
                        'category' => 'pottery',
                        'name' => 'Handmade Tagine',
                        'slug' => 'handmade-tagine',
                        'description' => "A handcrafted Moroccan tagine shaped in Fes for slow cooking and generous serving.\nIts weight and curve help distribute heat gently, while the hand-finished surface keeps the piece expressive and warm on the table.\nEach one carries slight differences in glaze and tone, which are part of the making process rather than defects.",
                        'price' => 177.00,
                        'stock' => 13,
                        'images' => [
                            'https://picsum.photos/id/1060/1200/900',
                            'https://picsum.photos/id/1062/1200/900',
                            'https://picsum.photos/id/1059/1200/900',
                        ],
                    ],
                    [
                        'category' => 'ceramics',
                        'name' => 'Decorative Vase',
                        'slug' => 'decorative-vase',
                        'description' => "A tall ceramic vase with a quiet silhouette and a hand-finished neck.\nDesigned for branches, dried stems, or display on its own, it brings a gallery-like calm to shelves and corners.\nThe glaze stays intentionally soft so the shape remains the first thing you notice.",
                        'price' => 245.00,
                        'stock' => 6,
                        'images' => [
                            'https://picsum.photos/id/1074/1200/900',
                            'https://picsum.photos/id/1080/1200/900',
                        ],
                    ],
                    [
                        'category' => 'pottery',
                        'name' => 'Ceramic Bowl Set',
                        'slug' => 'ceramic-bowl-set',
                        'description' => "A set of serving bowls made for daily meals, fruit, salads, and shared dishes.\nThe form is simple, but the hand-finishing keeps each bowl from feeling factory-made.\nThey stack well, clean easily, and still carry the character of handmade pottery.",
                        'price' => 132.00,
                        'stock' => 9,
                        'images' => [
                            'https://picsum.photos/id/1081/1200/900',
                            'https://picsum.photos/id/1082/1200/900',
                        ],
                    ],
                    [
                        'category' => 'ceramics',
                        'name' => 'Traditional Plate',
                        'slug' => 'traditional-plate',
                        'description' => "A decorative serving plate inspired by classic Moroccan tableware.\nIt works both as a serving piece and as a wall object in a dining room or kitchen.\nThe hand-painted balance is intentionally imperfect, which gives the plate its life.",
                        'price' => 118.00,
                        'stock' => 4,
                        'images' => [
                            'https://picsum.photos/id/1040/1200/900',
                            'https://picsum.photos/id/1039/1200/900',
                        ],
                    ],
                ],
            ],
            [
                'user' => [
                    'email' => 'fatima.weaver@lamsa.ma',
                    'name' => 'Fatima Weaver',
                ],
                'artisan' => [
                    'bio' => "Fatima works between Rabat and Salé, weaving pieces that balance traditional rhythm with quieter contemporary palettes.\nHer textiles are made for homes that want softness, texture, and a visible human hand.",
                    'city' => 'Rabat',
                    'region' => 'Rabat-Sale-Kenitra',
                    'craft_type' => 'weaving',
                ],
                'store' => [
                    'name' => 'Fatima Loom Studio',
                    'slug' => 'fatima-loom-studio',
                    'description' => 'Woven textiles, throws, and rugs made with calm palettes and tactile finishes.',
                    'logo' => 'https://picsum.photos/id/1027/400/400',
                ],
                'products' => [
                    [
                        'category' => 'weaving',
                        'name' => 'Wool Kilim Runner',
                        'slug' => 'wool-kilim-runner',
                        'description' => "A narrow woven runner designed for hallways, bedsides, and long entry spaces.\nThe palette is warm and restrained, so it adds texture without overwhelming the room.\nEvery pass on the loom leaves small shifts that make the surface feel alive.",
                        'price' => 390.00,
                        'stock' => 3,
                        'images' => [
                            'https://picsum.photos/id/1073/1200/900',
                            'https://picsum.photos/id/1070/1200/900',
                        ],
                    ],
                    [
                        'category' => 'weaving',
                        'name' => 'Handwoven Cushion Cover',
                        'slug' => 'handwoven-cushion-cover',
                        'description' => "A woven cushion cover that gives sofas and reading corners a handmade anchor.\nIts texture is meant to be seen up close and used daily.\nThe weave keeps its structure while still feeling soft in a lived-in room.",
                        'price' => 84.00,
                        'stock' => 15,
                        'images' => [
                            'https://picsum.photos/id/1068/1200/900',
                            'https://picsum.photos/id/1067/1200/900',
                        ],
                    ],
                    [
                        'category' => 'weaving',
                        'name' => 'Natural Wool Throw',
                        'slug' => 'natural-wool-throw',
                        'description' => "A soft throw woven in natural wool tones for beds, benches, and winter evenings.\nIt is intentionally understated, with the texture doing most of the visual work.\nThe finish stays honest to the fiber, so it feels warm and grounded rather than polished.",
                        'price' => 210.00,
                        'stock' => 8,
                        'images' => [
                            'https://picsum.photos/id/1066/1200/900',
                            'https://picsum.photos/id/1065/1200/900',
                        ],
                    ],
                    [
                        'category' => 'leather',
                        'name' => 'Woven Leather Pouf',
                        'slug' => 'woven-leather-pouf',
                        'description' => "A low pouf that combines woven structure with leather finishing.\nIt works as extra seating, a footrest, or simply a textured object in a room.\nThe handwork is visible in the joining, not hidden behind machine-perfect seams.",
                        'price' => 520.00,
                        'stock' => 2,
                        'images' => [
                            'https://picsum.photos/id/1033/1200/900',
                            'https://picsum.photos/id/1032/1200/900',
                        ],
                    ],
                ],
            ],
            [
                'user' => [
                    'email' => 'youssef.wood@lamsa.ma',
                    'name' => 'Youssef Ben Omar',
                ],
                'artisan' => [
                    'bio' => "Youssef carves cedar and walnut into small furniture objects and everyday pieces.\nHis workshop practice focuses on proportion, joinery, and the quiet elegance of useful objects.",
                    'city' => 'Marrakech',
                    'region' => 'Marrakech-Safi',
                    'craft_type' => 'woodwork',
                ],
                'store' => [
                    'name' => 'Atelier Youssef',
                    'slug' => 'atelier-youssef',
                    'description' => 'Woodwork, carved home pieces, and small furniture accents made in Marrakech.',
                    'logo' => 'https://picsum.photos/id/177/400/400',
                ],
                'products' => [
                    [
                        'category' => 'woodwork',
                        'name' => 'Carved Cedar Tray',
                        'slug' => 'carved-cedar-tray',
                        'description' => "A cedar serving tray carved for tea service, coffee tables, and layered shelf styling.\nThe shape remains simple while the handwork sits in the carved border and finishing.\nIt is meant to age well and grow richer with use.",
                        'price' => 160.00,
                        'stock' => 11,
                        'images' => [
                            'https://picsum.photos/id/24/1200/900',
                            'https://picsum.photos/id/25/1200/900',
                        ],
                    ],
                    [
                        'category' => 'woodwork',
                        'name' => 'Walnut Spice Box',
                        'slug' => 'walnut-spice-box',
                        'description' => "A compartment box for spices, tea, or small table objects.\nThe walnut grain is left visible so the material does the storytelling.\nInside and outside are sanded by hand for a finish that feels smooth without feeling sterile.",
                        'price' => 134.00,
                        'stock' => 7,
                        'images' => [
                            'https://picsum.photos/id/26/1200/900',
                            'https://picsum.photos/id/27/1200/900',
                        ],
                    ],
                    [
                        'category' => 'leather',
                        'name' => 'Leather Journal Cover',
                        'slug' => 'leather-journal-cover',
                        'description' => "A stitched leather cover for notebooks and travel journals.\nIt is built to gather marks over time, becoming more personal with use.\nThe edges are finished by hand to keep the cover durable and tactile.",
                        'price' => 98.00,
                        'stock' => 12,
                        'images' => [
                            'https://picsum.photos/id/29/1200/900',
                            'https://picsum.photos/id/28/1200/900',
                        ],
                    ],
                    [
                        'category' => 'jewelry',
                        'name' => 'Silver Atlas Pendant',
                        'slug' => 'silver-atlas-pendant',
                        'description' => "A small silver pendant with a quiet silhouette and handmade finishing.\nIt layers well, wears easily, and feels personal without being ornate.\nThe final polish stays soft so the handcrafted details remain visible.",
                        'price' => 145.00,
                        'stock' => 5,
                        'images' => [
                            'https://picsum.photos/id/30/1200/900',
                            'https://picsum.photos/id/31/1200/900',
                        ],
                    ],
                ],
            ],
        ];

        $products = [];

        foreach ($artisans as $artisanDefinition) {
            $artisanUser = User::updateOrCreate(
                ['email' => $artisanDefinition['user']['email']],
                [
                    'name' => $artisanDefinition['user']['name'],
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                ]
            );
            $artisanUser->assignRole('artisan');

            $artisan = Artisan::updateOrCreate(
                ['user_id' => $artisanUser->id],
                [
                    'bio' => $artisanDefinition['artisan']['bio'],
                    'city' => $artisanDefinition['artisan']['city'],
                    'region' => $artisanDefinition['artisan']['region'],
                    'status' => ArtisanStatus::Active,
                    'craft_type' => $artisanDefinition['artisan']['craft_type'],
                ]
            );

            $store = Store::updateOrCreate(
                ['slug' => $artisanDefinition['store']['slug']],
                [
                    'artisan_id' => $artisan->id,
                    'name' => $artisanDefinition['store']['name'],
                    'description' => $artisanDefinition['store']['description'],
                    'logo' => $artisanDefinition['store']['logo'],
                    'is_active' => true,
                ]
            );

            foreach ($artisanDefinition['products'] as $productDefinition) {
                $product = Product::updateOrCreate(
                    ['slug' => $productDefinition['slug']],
                    [
                        'store_id' => $store->id,
                        'artisan_id' => $artisan->id,
                        'category_id' => $categories[$productDefinition['category']]->id,
                        'name' => $productDefinition['name'],
                        'description' => $productDefinition['description'],
                        'price' => $productDefinition['price'],
                        'stock' => $productDefinition['stock'],
                        'images' => $productDefinition['images'],
                        'status' => ProductStatus::Active,
                        'is_published' => true,
                    ]
                );

                $products[$productDefinition['slug']] = $product;
            }
        }

        return $products;
    }

    private function seedReviews(array $buyers, array $products): void
    {
        $reviewDefinitions = [
            ['buyer' => 'buyer@lamsa.ma', 'product' => 'handmade-tagine', 'rating' => 5, 'comment' => 'Beautiful craftsmanship. The tagine feels solid, balanced, and looks even better on the table than in the photos.'],
            ['buyer' => 'sara.buyer@lamsa.ma', 'product' => 'handmade-tagine', 'rating' => 4, 'comment' => 'Lovely piece with a warm finish. Shipping took a little time, but the quality was worth it.'],
            ['buyer' => 'amina.home@lamsa.ma', 'product' => 'decorative-vase', 'rating' => 5, 'comment' => 'Exactly what I wanted for my living room shelf. It has that handmade presence without feeling heavy.'],
            ['buyer' => 'leo.curator@lamsa.ma', 'product' => 'ceramic-bowl-set', 'rating' => 4, 'comment' => 'Great everyday bowls with a nice artisanal touch. The glaze differences are subtle and beautiful.'],
            ['buyer' => 'youssef.collector@lamsa.ma', 'product' => 'traditional-plate', 'rating' => 5, 'comment' => 'The hand-painted finish gives this plate real character. It works perfectly as wall decor.'],
            ['buyer' => 'buyer@lamsa.ma', 'product' => 'wool-kilim-runner', 'rating' => 5, 'comment' => 'Rich texture and excellent colors. The runner changed the feel of my hallway immediately.'],
            ['buyer' => 'sara.buyer@lamsa.ma', 'product' => 'natural-wool-throw', 'rating' => 4, 'comment' => 'Soft, warm, and understated. It feels handmade in the best way.'],
            ['buyer' => 'amina.home@lamsa.ma', 'product' => 'woven-leather-pouf', 'rating' => 5, 'comment' => 'The finishing is excellent and the piece feels sturdy. It looks beautiful beside a sofa.'],
            ['buyer' => 'leo.curator@lamsa.ma', 'product' => 'carved-cedar-tray', 'rating' => 5, 'comment' => 'A very elegant tray with beautiful wood grain. The proportions feel considered and refined.'],
            ['buyer' => 'youssef.collector@lamsa.ma', 'product' => 'walnut-spice-box', 'rating' => 4, 'comment' => 'Very practical and nicely made. The compartments are useful and the walnut finish is lovely.'],
            ['buyer' => 'buyer@lamsa.ma', 'product' => 'silver-atlas-pendant', 'rating' => 5, 'comment' => 'Small but full of character. The finish feels handcrafted and not over-polished.'],
            ['buyer' => 'sara.buyer@lamsa.ma', 'product' => 'leather-journal-cover', 'rating' => 4, 'comment' => 'Beautiful leather and strong stitching. It feels like something that will age very well.'],
        ];

        foreach ($reviewDefinitions as $definition) {
            Review::updateOrCreate(
                [
                    'user_id' => $buyers[$definition['buyer']]->id,
                    'product_id' => $products[$definition['product']]->id,
                ],
                [
                    'rating' => $definition['rating'],
                    'comment' => $definition['comment'],
                ]
            );
        }
    }

    private function seedOrders(array $buyers, array $products): void
    {
        $orderDefinitions = [
            [
                'key' => 'seed-order-1',
                'buyer' => 'buyer@lamsa.ma',
                'product' => 'handmade-tagine',
                'status' => OrderStatus::Delivered,
                'quantity' => 1,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'shipping_address' => '24 Rue Talaa Kebira, Fes',
            ],
            [
                'key' => 'seed-order-2',
                'buyer' => 'sara.buyer@lamsa.ma',
                'product' => 'decorative-vase',
                'status' => OrderStatus::Delivered,
                'quantity' => 1,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'shipping_address' => '18 Avenue Mohammed V, Rabat',
            ],
            [
                'key' => 'seed-order-3',
                'buyer' => 'amina.home@lamsa.ma',
                'product' => 'wool-kilim-runner',
                'status' => OrderStatus::Processing,
                'quantity' => 1,
                'payment_method' => 'cash',
                'payment_status' => 'unpaid',
                'shipping_address' => '9 Rue Al Qods, Casablanca',
            ],
            [
                'key' => 'seed-order-4',
                'buyer' => 'leo.curator@lamsa.ma',
                'product' => 'carved-cedar-tray',
                'status' => OrderStatus::Delivered,
                'quantity' => 2,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'shipping_address' => '41 Rue Yves Saint Laurent, Marrakech',
            ],
            [
                'key' => 'seed-order-5',
                'buyer' => 'youssef.collector@lamsa.ma',
                'product' => 'silver-atlas-pendant',
                'status' => OrderStatus::Shipped,
                'quantity' => 1,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'shipping_address' => '15 Boulevard Hassan II, Tangier',
            ],
        ];

        foreach ($orderDefinitions as $definition) {
            $product = $products[$definition['product']];
            $buyer = $buyers[$definition['buyer']];
            $orderTotal = (float) $product->price * $definition['quantity'];

            $order = Order::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'artisan_id' => $product->artisan_id,
                    'notes' => $definition['key'],
                ],
                [
                    'status' => $definition['status'],
                    'total_amount' => $orderTotal,
                    'shipping_address' => $definition['shipping_address'],
                    'payment_method' => $definition['payment_method'],
                    'payment_status' => $definition['payment_status'],
                ]
            );

            OrderItem::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'artisan_id' => $product->artisan_id,
                    'quantity' => $definition['quantity'],
                    'unit_price' => $product->price,
                ]
            );
        }
    }

    private function seedAuctions(array $buyers, array $categories): void
    {
        $stores = Store::query()
            ->with('artisan')
            ->whereIn('slug', [
                'hassan-pottery',
                'fatima-loom-studio',
                'atelier-youssef',
            ])
            ->get()
            ->keyBy('slug');

        $auctionDefinitions = [
            [
                'slug' => 'atlas-ceramic-vessel-auction',
                'store_slug' => 'hassan-pottery',
                'category_slug' => 'ceramics',
                'name' => 'Atlas Ceramic Vessel',
                'description' => "A sculptural ceramic vessel opened for bidding directly from Hassan's workshop.\nThe piece balances decorative presence with a usable silhouette, making it suitable for shelves, entry consoles, or table styling.",
                'starting_price' => 180.00,
                'reserve_price' => 260.00,
                'images' => [
                    'https://picsum.photos/id/1050/1200/900',
                    'https://picsum.photos/id/1051/1200/900',
                ],
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(4),
                'buyers' => [
                    $buyers['buyer@lamsa.ma'],
                    $buyers['sara.buyer@lamsa.ma'],
                    $buyers['leo.curator@lamsa.ma'],
                ],
            ],
            [
                'slug' => 'woven-salon-runner-auction',
                'store_slug' => 'fatima-loom-studio',
                'category_slug' => 'weaving',
                'name' => 'Woven Salon Runner',
                'description' => "A limited woven runner released as a one-off auction lot.\nThe palette is soft and architectural, with enough texture to anchor a room without overwhelming it.",
                'starting_price' => 320.00,
                'reserve_price' => 470.00,
                'images' => [
                    'https://picsum.photos/id/1048/1200/900',
                    'https://picsum.photos/id/1049/1200/900',
                ],
                'starts_at' => now()->subHours(18),
                'ends_at' => now()->addDays(2),
                'buyers' => [
                    $buyers['amina.home@lamsa.ma'],
                    $buyers['youssef.collector@lamsa.ma'],
                    $buyers['buyer@lamsa.ma'],
                    $buyers['sara.buyer@lamsa.ma'],
                ],
            ],
            [
                'slug' => 'carved-cedar-keepsake-auction',
                'store_slug' => 'atelier-youssef',
                'category_slug' => 'woodwork',
                'name' => 'Carved Cedar Keepsake Box',
                'description' => "A cedar keepsake box carved in Marrakech and offered as a live auction piece.\nThe value is in the joinery, carving rhythm, and the way the lid closes with a quiet, precise fit.",
                'starting_price' => 140.00,
                'reserve_price' => 210.00,
                'images' => [
                    'https://picsum.photos/id/103/1200/900',
                    'https://picsum.photos/id/107/1200/900',
                ],
                'starts_at' => now()->subHours(8),
                'ends_at' => now()->addHours(18),
                'buyers' => [
                    $buyers['leo.curator@lamsa.ma'],
                    $buyers['buyer@lamsa.ma'],
                    $buyers['amina.home@lamsa.ma'],
                ],
            ],
        ];

        foreach ($auctionDefinitions as $definition) {
            if (Auction::query()->where('slug', $definition['slug'])->exists()) {
                continue;
            }

            $store = $stores[$definition['store_slug']];

            Auction::factory()
                ->state([
                    'store_id' => $store->id,
                    'artisan_id' => $store->artisan_id,
                    'category_id' => $categories[$definition['category_slug']]->id,
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'description' => $definition['description'],
                    'images' => $definition['images'],
                    'starting_price' => $definition['starting_price'],
                    'current_price' => $definition['starting_price'],
                    'reserve_price' => $definition['reserve_price'],
                    'starts_at' => $definition['starts_at'],
                    'ends_at' => $definition['ends_at'],
                    'is_published' => true,
                ])
                ->withBids(count($definition['buyers']), $definition['buyers'])
                ->create();
        }
    }
}
