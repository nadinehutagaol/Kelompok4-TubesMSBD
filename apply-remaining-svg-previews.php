<?php
// Run only in Trrrible-Uji: php apply-remaining-svg-previews.php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$slugs = ['aura-mobile-ui-kit', 'studio-branding-pack', 'essential-icon-pack', 'creative-social-media-kit', 'creative-illustration-set', 'abstract-shape-illustrations', 'retro-display-lettering', 'organic-brand-shapes'];
$updated = 0;
foreach ($slugs as $slug) {
    $newPath = "marketplace/previews/{$slug}.webp";
    $oldPath = "marketplace/previews/{$slug}.svg";
    if (!Illuminate\Support\Facades\Storage::disk('public')->exists($newPath)) {
        echo "SKIP missing image: {$slug}\n";
        continue;
    }
    $product = App\Models\Marketplace\Product::where('slug', $slug)->first();
    if (!$product) { echo "SKIP missing product: {$slug}\n"; continue; }
    if ($product->preview_path === $newPath) { echo "OK already updated: {$slug}\n"; continue; }
    if ($product->preview_path !== $oldPath) {
        echo "SKIP existing non-SVG/custom image: {$slug}\n";
        continue;
    }
    $product->preview_path = $newPath;
    $product->save();
    echo "UPDATED: {$slug}\n";
    $updated++;
}
echo "Done: {$updated} previews updated.\n";
