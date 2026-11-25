@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div class="marketplace-lilac-hero flex flex-col md:flex-row md:items-end md:justify-between gap-5 mb-8">
        <div><p class="text-sm font-bold tracking-widest text-[#9060d4] uppercase">Trrrible Creative Market</p><h1 class="text-4xl font-black text-[#261a3b] mt-2">Find your next creative asset.</h1><p class="text-gray-500 mt-2">Discover digital resources made by independent designers.</p></div>
        @auth<a href="{{ route('marketplace.create') }}" class="inline-flex justify-center rounded-full bg-[#261a3b] text-white px-6 py-3 font-bold hover:bg-[#9060d4]">+ Sell a product</a>@else<a href="{{ route('login') }}" class="rounded-full bg-[#261a3b] text-white px-6 py-3 font-bold">Sign in to sell</a>@endauth
    </div>
    <form method="GET" class="bg-white border border-gray-100 rounded-2xl p-4 grid md:grid-cols-4 gap-3 mb-8 shadow-sm">
        <input name="q" value="{{ request('q') }}" placeholder="Search UI kits, mockups..." class="md:col-span-2 rounded-xl border-gray-200">
        <select name="category" class="rounded-xl border-gray-200"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category')===$category->slug)>{{ $category->name }}</option>@endforeach</select>
        <select name="sort" class="rounded-xl border-gray-200"><option value="latest">Latest</option><option value="price_low" @selected(request('sort')==='price_low')>Price: low to high</option><option value="price_high" @selected(request('sort')==='price_high')>Price: high to low</option></select>
        <button class="md:col-span-4 bg-[#9060d4] text-white rounded-xl py-3 font-bold">Search marketplace</button>
    </form>
    @if($products->count())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
        <a href="{{ route('marketplace.show',$product) }}"
   class="marketplace-lilac-card group bg-white
          hover:!bg-[#EDE0FF]
          rounded-2xl overflow-hidden
          border border-gray-100 hover:border-[#B794F4]
          hover:shadow-[0_12px_30px_rgba(144,96,212,0.25)]
          hover:-translate-y-1
          transition-all duration-300">
            <div class="aspect-[4/3] bg-gray-100 overflow-hidden">@if($product->preview_path)<img src="{{ asset('storage/' . $product->preview_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="{{ $product->title }}">@else<div class="w-full h-full flex items-center justify-center text-5xl text-gray-300">✦</div>@endif</div>
            <div class="p-4"><div class="text-xs text-[#9060d4] font-bold">{{ $product->category?->name ?? 'Digital asset' }}</div><h2 class="font-bold text-[#261a3b] mt-1 line-clamp-1">{{ $product->title }}</h2><p class="text-xs text-gray-500 mt-1">by {{ $product->seller->full_name }}</p><div class="flex justify-between items-center mt-4"><span class="font-black text-lg">Rp{{ number_format($product->price,0,',','.') }}</span><span class="text-xs text-gray-400">{{ $product->sales_count }} sales</span></div></div>
        </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
    @else<div class="bg-white rounded-2xl p-12 text-center text-gray-500">No products found yet. Be the first designer to publish a digital product.</div>@endif
</div>
@endsection
