<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Marketplace\Product;
use App\Models\Marketplace\ProductCategory;

class MarketplaceProductsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->take(4)->get();

        if ($users->count() === 0) {
            throw new \RuntimeException('Belum ada user untuk menjadi seller.');
        }

        $products = [
            [
                'title' => 'Aura Mobile UI Kit',
                'category' => 'UI Kits',
                'price' => 149000,
                'stock' => 10,
                'description' => 'A modern mobile interface kit with clean layouts and soft creative colors.',
            ],
            [
                'title' => 'Studio Branding Pack',
                'category' => 'Templates',
                'price' => 89000,
                'stock' => 15,
                'description' => 'A complete branding template collection for creative studios and businesses.',
            ],
            [
                'title' => 'Essential Icon Pack',
                'category' => 'Icons & Illustrations',
                'price' => 49000,
                'stock' => 25,
                'description' => 'A versatile collection of minimal icons for digital products.',
            ],
            [
                'title' => 'Device Mockup Collection',
                'category' => 'Mockups',
                'price' => 69000,
                'stock' => 12,
                'description' => 'Professional device mockups for presenting your creative projects.',
            ],
            [
                'title' => 'Creative Social Media Kit',
                'category' => 'Templates',
                'price' => 59000,
                'stock' => 20,
                'description' => 'Editable social media templates with a modern visual direction.',
            ],
            [
                'title' => 'Minimal Portfolio Template',
                'category' => 'Templates',
                'price' => 119000,
                'stock' => 8,
                'description' => 'A clean portfolio template for independent designers.',
            ],
            [
                'title' => 'Modern Typography Bundle',
                'category' => 'Fonts & Graphics',
                'price' => 79000,
                'stock' => 18,
                'description' => 'A creative typography resource bundle for branding projects.',
            ],
            [
                'title' => 'Creative Illustration Set',
                'category' => 'Icons & Illustrations',
                'price' => 99000,
                'stock' => 14,
                'description' => 'A collection of illustrations for web and mobile design.',
            ],
            [
                'title' => 'Lilac Dashboard UI Kit',
                'category' => 'UI Kits',
                'price' => 179000,
                'stock' => 14,
                'description' => 'Dashboard components, charts and responsive panels in a soft lilac palette.',
            ],
            [
                'title' => 'SaaS Analytics UI System',
                'category' => 'UI Kits',
                'price' => 219000,
                'stock' => 12,
                'description' => 'A complete analytics interface with KPI cards and data visualizations.',
            ],
            [
                'title' => 'Travel Booking App UI',
                'category' => 'UI Kits',
                'price' => 139000,
                'stock' => 18,
                'description' => 'Mobile booking screens for destinations, itineraries and reservations.',
            ],
            [
                'title' => 'Wellness Mobile UI Kit',
                'category' => 'UI Kits',
                'price' => 159000,
                'stock' => 16,
                'description' => 'Calm wellness app screens for habits, journaling and progress.',
            ],
            [
                'title' => 'Creative Pitch Deck',
                'category' => 'Templates',
                'price' => 79000,
                'stock' => 25,
                'description' => 'Presentation slides for agencies, portfolios and client proposals.',
            ],
            [
                'title' => 'Editorial Portfolio Pages',
                'category' => 'Templates',
                'price' => 99000,
                'stock' => 22,
                'description' => 'Editorial-style layouts for designer portfolios and case studies.',
            ],
            [
                'title' => 'Social Campaign Planner',
                'category' => 'Templates',
                'price' => 59000,
                'stock' => 28,
                'description' => 'A cohesive social media campaign planning template.',
            ],
            [
                'title' => 'Minimal Resume Bundle',
                'category' => 'Templates',
                'price' => 39000,
                'stock' => 32,
                'description' => 'Clean resume and cover letter layouts for creative professionals.',
            ],
            [
                'title' => 'Pastel 3D Icon Set',
                'category' => 'Icons & Illustrations',
                'price' => 89000,
                'stock' => 20,
                'description' => 'Soft dimensional icons for dashboards, websites and apps.',
            ],
            [
                'title' => 'Abstract Shape Illustrations',
                'category' => 'Icons & Illustrations',
                'price' => 69000,
                'stock' => 26,
                'description' => 'Colorful abstract illustrations for landing pages and presentations.',
            ],
            [
                'title' => 'Business Line Icons',
                'category' => 'Icons & Illustrations',
                'price' => 49000,
                'stock' => 30,
                'description' => 'A consistent set of outline icons for modern products.',
            ],
            [
                'title' => 'Botanical Illustration Pack',
                'category' => 'Icons & Illustrations',
                'price' => 99000,
                'stock' => 16,
                'description' => 'Decorative botanical illustrations for digital brand identities.',
            ],
            [
                'title' => 'Laptop Screen Mockups',
                'category' => 'Mockups',
                'price' => 79000,
                'stock' => 19,
                'description' => 'Realistic laptop layouts for showcasing web design work.',
            ],
            [
                'title' => 'Packaging Brand Mockups',
                'category' => 'Mockups',
                'price' => 109000,
                'stock' => 15,
                'description' => 'Product packaging scenes for identity presentations.',
            ],
            [
                'title' => 'Mobile App Showcase Mockups',
                'category' => 'Mockups',
                'price' => 69000,
                'stock' => 24,
                'description' => 'Phone screen compositions for app launches and portfolios.',
            ],
            [
                'title' => 'Stationery Identity Mockups',
                'category' => 'Mockups',
                'price' => 89000,
                'stock' => 18,
                'description' => 'Elegant stationery compositions for branding previews.',
            ],
            [
                'title' => 'Retro Display Lettering',
                'category' => 'Fonts & Graphics',
                'price' => 59000,
                'stock' => 23,
                'description' => 'Expressive retro-inspired lettering assets for headlines.',
            ],
            [
                'title' => 'Gradient Texture Collection',
                'category' => 'Fonts & Graphics',
                'price' => 49000,
                'stock' => 27,
                'description' => 'Dreamy gradient textures for backgrounds and social media.',
            ],
            [
                'title' => 'Geometric Poster Graphics',
                'category' => 'Fonts & Graphics',
                'price' => 69000,
                'stock' => 21,
                'description' => 'Bold geometric compositions for posters and digital artwork.',
            ],
            [
                'title' => 'Organic Brand Shapes',
                'category' => 'Fonts & Graphics',
                'price' => 39000,
                'stock' => 29,
                'description' => 'A versatile set of organic decorative vector-style graphics.',
            ],
        ];

        $colors = [
            ['#dbeafe', '#6366f1'],
            ['#fce7f3', '#db2777'],
            ['#e0f2fe', '#0284c7'],
            ['#ede9fe', '#7c3aed'],
            ['#fce7f3', '#ec4899'],
            ['#dcfce7', '#16a34a'],
            ['#fef3c7', '#d97706'],
            ['#ffedd5', '#ea580c'],
        ];

        Storage::disk('public')->makeDirectory('marketplace/previews');

        foreach ($products as $index => $item) {
            $category = ProductCategory::where('name', $item['category'])
                ->firstOrFail();

            $seller = $users[$index % $users->count()];
            $slug = \Illuminate\Support\Str::slug($item['title']);

            $filePath = 'marketplace/demo/' . $slug . '.txt';
            $previewPath = 'marketplace/previews/' . $slug . '.svg';

            Storage::put(
                $filePath,
                "TRRRIBLE DIGITAL PRODUCT\n\n" .
                $item['title'] . "\n\n" .
                $item['description'] . "\n\n" .
                "This is a demonstration digital product for the MSBD project."
            );

            [$light, $accent] = $colors[$index % count($colors)];

            $safeTitle = htmlspecialchars(
                $item['title'],
                ENT_QUOTES | ENT_XML1,
                'UTF-8'
            );

            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="$light"/>
            <stop offset="100%" stop-color="#ffffff"/>
        </linearGradient>
    </defs>
    <rect width="800" height="600" fill="url(#bg)"/>
    <circle cx="680" cy="90" r="170" fill="$accent" opacity=".13"/>
    <circle cx="90" cy="540" r="150" fill="$accent" opacity=".12"/>
    <rect x="160" y="105" width="480" height="320" rx="30" fill="#ffffff" opacity=".9"/>
    <rect x="190" y="135" width="420" height="260" rx="20" fill="$light"/>
    <rect x="220" y="165" width="130" height="190" rx="16" fill="$accent" opacity=".85"/>
    <rect x="375" y="165" width="200" height="28" rx="10" fill="$accent" opacity=".65"/>
    <rect x="375" y="215" width="155" height="16" rx="8" fill="$accent" opacity=".35"/>
    <rect x="375" y="250" width="175" height="16" rx="8" fill="$accent" opacity=".35"/>
    <rect x="375" y="295" width="100" height="42" rx="14" fill="$accent"/>
    <text x="400" y="485" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="bold" fill="#0d0c22">$safeTitle</text>
    <text x="400" y="525" text-anchor="middle" font-family="Arial, sans-serif" font-size="17" fill="#64748b">TRRRIBLE CREATIVE MARKET</text>
</svg>
SVG;

            Storage::disk('public')->put($previewPath, $svg);

            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'seller_id' => $seller->id,
                    'category_id' => $category->id,
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'file_path' => $filePath,
                    'is_active' => true,
                ]
            );

            // Never overwrite an existing product preview or any stock/sales values.
            if (!$product->preview_path) {
                $product->preview_path = $previewPath;
                $product->save();
            }
        }
    }
}
