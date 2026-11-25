@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <a href="{{ route('marketplace.index') }}" class="text-sm font-bold text-[#9060d4]">← Back to Marketplace</a>
    <div class="grid lg:grid-cols-5 gap-10 mt-5">
        <div class="lg:col-span-3"><div class="rounded-3xl overflow-hidden bg-white border border-gray-100 aspect-[4/3]">@if($product->preview_path)<img src="{{ asset('storage/' . $product->preview_path) }}" class="w-full h-full object-cover" alt="{{ $product->title }}">@else<div class="h-full flex items-center justify-center text-7xl text-gray-300">✦</div>@endif</div><div class="bg-white rounded-2xl p-6 mt-6"><h2 class="font-black text-xl">About this asset</h2><p class="text-gray-600 whitespace-pre-line mt-3">{{ $product->description }}</p></div></div>
        <aside class="lg:col-span-2"><div class="bg-white border border-gray-100 rounded-3xl p-6 sticky top-24"><p class="text-sm text-[#9060d4] font-bold">{{ $product->category?->name ?? 'Digital asset' }}</p><h1 class="text-3xl font-black mt-2">{{ $product->title }}</h1><p class="text-gray-500 mt-3">Created by <a class="font-bold text-[#261a3b]" href="{{ route('user.profile',$product->seller->username) }}">{{ $product->seller->full_name }}</a></p><div class="text-3xl font-black mt-7">Rp{{ number_format($product->price,0,',','.') }}</div><p class="text-xs text-gray-400 mt-1">One-time purchase · Digital download</p>
            @auth
                @if(auth()->id() !== $product->seller_id)
                <form method="POST" action="{{ route('marketplace.cart.add',$product) }}" class="mt-6">@csrf<button class="w-full rounded-full bg-[#9060d4] text-white py-3 font-bold hover:bg-[#7545b9]">Add to cart</button></form>
                @else<a href="{{ route('marketplace.seller') }}" class="block text-center rounded-full bg-gray-100 py-3 mt-6 font-bold">Manage my product</a>@endif
            @else<a href="{{ route('login') }}" class="block text-center rounded-full bg-[#261a3b] text-white py-3 mt-6 font-bold">Sign in to purchase</a>@endauth
            <div class="border-t mt-6 pt-5 text-sm text-gray-500 flex justify-between"><span>{{ $product->sales_count }} purchases</span><span>{{ $product->stock }} available</span></div>
        </div></aside>
    </div>
    @if($related->count())<h2 class="text-2xl font-black mt-14 mb-5">More to explore</h2><div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">@foreach($related as $item)<a href="{{ route('marketplace.show',$item) }}" class="bg-white rounded-2xl p-4 border"><div class="font-bold">{{ $item->title }}</div><div class="text-sm text-gray-500 mt-1">Rp{{ number_format($item->price,0,',','.') }}</div></a>@endforeach</div>@endif
</div>
@endsection
