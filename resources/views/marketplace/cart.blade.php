
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <h1 class="text-3xl font-black">Shopping cart</h1>
    <p class="text-gray-500 mt-2">
        Review your digital assets before checkout.
    </p>

    {{-- Pesan kesalahan checkout --}}
    @if($errors->has('cart'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mt-5">
            <p class="font-bold">
                Stok produk tidak mencukupi.
            </p>
            <p class="text-sm mt-1">
                {{ $errors->first('cart') }}
            </p>
        </div>
    @endif

    @if($cart->items->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center mt-7">
            <p class="text-gray-500">Your cart is empty.</p>
            <a href="{{ route('marketplace.index') }}"
               class="inline-block mt-4 text-[#9060d4] font-bold">
                Explore Marketplace →
            </a>
        </div>
    @else

        <div class="space-y-4 mt-7">
            @php($total = 0)

            @foreach($cart->items as $item)
                @php($total += $item->product->price * $item->quantity)

                <div class="bg-white border rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                    <div>
                        <h2 class="font-bold">
                            {{ $item->product->title }}
                        </h2>

                        <p class="text-sm text-gray-500">
                            by {{ $item->product->seller->full_name }}
                            · Rp{{ number_format($item->product->price, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">

                        <form method="POST"
                              action="{{ route('marketplace.cart.update', $item) }}"
                              class="flex gap-2">
                            @csrf
                            @method('PATCH')

                            <input type="number"
                                   name="quantity"
                                   min="1"
                                   max="20"
                                   value="{{ $item->quantity }}"
                                   class="w-20 rounded-lg border-gray-200">

                            <button class="text-sm font-bold text-[#9060d4]">
                                Update
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('marketplace.cart.remove', $item) }}">
                            @csrf
                            @method('DELETE')

                            <button class="text-sm text-red-500">
                                Remove
                            </button>
                        </form>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl p-6 mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">

            <div>
                <p class="text-gray-500">Total</p>
                <p class="text-2xl font-black">
                    Rp{{ number_format($total, 0, ',', '.') }}
                </p>
            </div>

            <form method="POST"
                  action="{{ route('marketplace.checkout') }}"
                  onsubmit="return confirm('Continue with simulated payment?')">
                @csrf

                <button class="rounded-full bg-[#9060d4] text-white px-8 py-3 font-bold">
                    Checkout · Simulated payment
                </button>
            </form>

        </div>

    @endif
</div>
@endsection
