<?php
// Run ONLY in the test project: php apply-demo-previews.php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$slugs = ['lilac-dashboard-ui-kit', 'social-campaign-planner', 'editorial-portfolio-pages', 'packaging-brand-mockups', 'creative-pitch-deck', 'minimal-resume-bundle', 'stationery-identity-mockups', 'minimal-portfolio-template', 'botanical-illustration-pack', 'geometric-poster-graphics', 'wellness-mobile-ui-kit', 'business-line-icons', 'modern-typography-bundle', 'laptop-screen-mockups', 'pastel-3d-icon-set', 'gradient-texture-collection', 'device-mockup-collection', 'travel-booking-app-ui', 'saas-analytics-ui-system', 'mobile-app-showcase-mockups'];
$updated = 0;
foreach ($slugs as $slug) {
    $path = "marketplace/previews/{$slug}.webp";
    if (!Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        echo "SKIP missing file: {$path}\n";
        continue;
    }
    $product = App\Models\Marketplace\Product::where('slug', $slug)->first();
    if (!$product) { echo "SKIP missing product: {$slug}\n"; continue; }
    $old = $product->preview_path;
    if ($old && $old !== "marketplace/previews/{$slug}.svg" && $old !== $path) {
        echo "SKIP custom preview: {$slug}\n";
        continue;
    }
    if ($old === $path) { echo "OK already updated: {$slug}\n"; continue; }
    $product->preview_path = $path;
    $product->save();
    echo "UPDATED: {$slug}\n";
    $updated++;
}
echo "Done: {$updated} previews updated.\n";
