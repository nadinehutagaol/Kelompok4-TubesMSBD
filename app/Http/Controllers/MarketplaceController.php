<?php

namespace App\Http\Controllers;

use App\Models\Marketplace\Cart;
use App\Models\Marketplace\Order;
use App\Models\Marketplace\Payment;
use App\Models\Marketplace\Product;
use App\Models\Marketplace\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::orderBy('name')->get();
        $products = Product::with(['seller', 'category'])
            ->where('is_active', true)
            ->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('title', 'ilike', '%'.$request->q.'%')
                    ->orWhere('description', 'ilike', '%'.$request->q.'%');
            }))

->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->category)))
->when($request->input('sort') === 'price_low', fn ($q) => $q->orderBy('price'))
->when($request->input('sort') === 'price_high', fn ($q) => $q->orderByDesc('price'))
->when(!in_array($request->input('sort'), ['price_low', 'price_high']), fn ($q) => $q->latest())
->paginate(12)->withQueryString();


        return view('marketplace.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load(['seller', 'category', 'reviews.user']);
        $related = Product::with('seller')->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->latest()->limit(4)->get();
        return view('marketplace.show', compact('product', 'related'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        return view('marketplace.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:160'],
            'description' => ['required','string','max:5000'],
            'price' => ['required','numeric','min:0','max:9999999999'],
            'category_id' => ['nullable','exists:product_categories,id'],
            'stock' => ['required','integer','min:1','max:1000000'],
            'preview' => ['nullable','image','max:5120'],
            'digital_file' => ['required','file','max:51200'],
        ]);

        $data['preview_path'] = $request->file('preview')?->store('marketplace/previews', 'public');
        $data['file_path'] = $request->file('digital_file')->store('marketplace/private');
        $data['seller_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(7));
        unset($data['preview'], $data['digital_file']);

        Product::create($data);
        return redirect()->route('marketplace.seller')->with('success', 'Produk berhasil diterbitkan di Marketplace.');
    }

 
public function seller(Request $request)
{
    $products = Product::where('seller_id', $request->user()->id)
        ->latest()
        ->paginate(10);

    $salesResult = DB::selectOne(
        'SELECT public.fn_my_marketplace_sales() AS sales'
    );

    $sales = $salesResult->sales ?? 0;

    return view('marketplace.seller', compact('products', 'sales'));
}


    public function cart(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.product.seller');
        return view('marketplace.cart', compact('cart'));
    }

    public function addToCart(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        if ($product->seller_id === $request->user()->id) {
            return back()->with('error', 'Kamu tidak dapat membeli produk milik sendiri.');
        }
        $data = $request->validate(['quantity' => ['nullable','integer','min:1','max:20']]);
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = min(20, ($item->exists ? $item->quantity : 0) + ($data['quantity'] ?? 1));
        $item->save();
        return redirect()->route('marketplace.cart')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function updateCart(Request $request, int $item)
    {
        $data = $request->validate(['quantity' => ['required','integer','min:1','max:20']]);
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $updated = $cart->items()->whereKey($item)->update($data);

abort_unless($updated, 404);
        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function removeCartItem(Request $request, int $item)
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $deleted = $cart->items()->whereKey($item)->delete();

abort_unless($deleted, 404);
        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    /** Checkout is atomic: inventory locks, order, order items, payment and cart cleanup commit together. */

public function checkout(Request $request)
{
    $userId = (int) $request->user()->id;

    $order = DB::transaction(function () use ($request, $userId) {
        // Lock keranjang agar checkout pengguna yang sama berjalan bergantian.
        $cart = Cart::where('user_id', $userId)
            ->lockForUpdate()
            ->firstOrFail();

        // Ambil item setelah keranjang berhasil dikunci.
        $lockedItems = $cart->items()
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        // Cegah pembuatan pesanan kosong.
        if ($lockedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang masih kosong atau sudah diproses.',
            ]);
        }

        $subtotalCents = 0;
        $lockedProducts = [];

        foreach ($lockedItems as $cartItem) {
            $product = Product::whereKey($cartItem->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product || !$product->is_active) {
                throw ValidationException::withMessages([
                    'cart' => 'Ada produk yang sudah tidak tersedia.',
                ]);
            }

            if ((int) $product->seller_id === $userId) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang berisi produk milik akun sendiri.',
                ]);
            }

            if ($product->stock < $cartItem->quantity) {
                throw ValidationException::withMessages([
                    'cart' => 'Stok ' . $product->title . ' tidak mencukupi.',
                ]);
            }

            $product->decrement('stock', $cartItem->quantity);
            $product->increment('sales_count', $cartItem->quantity);

            $unitPriceCents = (int) round((float) $product->price * 100);

$lineTotalCents = $unitPriceCents * (int) $cartItem->quantity;

            $subtotalCents += $lineTotalCents;

            $lockedProducts[] = [
    $product,
    $cartItem->quantity,
    number_format($lineTotalCents / 100, 2, '.', ''),
    $unitPriceCents,
];
        }

        $order = Order::create([
            'order_number' => 'TRR-' . now()->format('ymd') . '-' . Str::upper(Str::random(8)),
            'buyer_id' => $userId,
            'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
            'total' => number_format($subtotalCents / 100, 2, '.', ''),
            'status' => 'paid',
            'placed_at' => now(),
        ]);

        foreach ($lockedProducts as [$product, $quantity, $lineTotal, $unitPriceCents]) {
    $order->items()->create([
        'product_id' => $product->id,
        'seller_id' => $product->seller_id,
        'product_title' => $product->title,
        'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
        'quantity' => $quantity,
        'line_total' => $lineTotal,
    ]);
}

        Payment::create([
            'order_id' => $order->id,
            'method' => 'simulation',
            'status' => 'paid',
            'reference' => 'SIM-' . Str::upper(Str::random(12)),
            'paid_at' => now(),
        ]);

        $cart->items()->delete();

        return $order;
    }, 3);

    return redirect()
        ->route('marketplace.orders.show', $order)
        ->with('success', 'Pembayaran simulasi berhasil. Produk digital sudah tersedia untuk diunduh.');
}


    public function orders(Request $request)
    {
        $orders = Order::with(['items','payment'])->where('buyer_id', $request->user()->id)->latest()->paginate(10);
        return view('marketplace.orders', compact('orders'));
    }

public function orderShow(Request $request, $order)
{
    $order = Order::where('buyer_id', $request->user()->id)
        ->with(['items.product', 'payment'])
        ->findOrFail($order);

    return view('marketplace.order-show', compact('order'));
}

public function download(Request $request, $order, int $item)
{
    $order = Order::where('buyer_id', $request->user()->id)
        ->where('status', 'paid')
        ->findOrFail($order);

    $orderItem = $order->items()
        ->whereKey($item)
        ->with('product')
        ->firstOrFail();

    abort_unless(
        $orderItem->product &&
        Storage::exists($orderItem->product->file_path),
        404
    );

    return Storage::download(
        $orderItem->product->file_path,
        Str::slug($orderItem->product_title) . '-download'
    );
}
}
