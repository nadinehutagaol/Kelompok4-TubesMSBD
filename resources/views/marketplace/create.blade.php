@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10"><a href="{{ route('marketplace.seller') }}" class="text-sm font-bold text-[#9060d4]">← My products</a><h1 class="text-3xl font-black mt-3">Publish a digital product</h1><p class="text-gray-500 mt-2">Upload a preview and the digital file customers will receive after checkout.</p>
@if($errors->any())<div class="bg-red-50 text-red-700 rounded-xl p-4 mt-5">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" action="{{ route('marketplace.store') }}" enctype="multipart/form-data" class="bg-white border rounded-2xl p-6 mt-6 space-y-5">@csrf
<div><label class="block font-bold mb-2">Product title</label><input name="title" value="{{ old('title') }}" required maxlength="160" class="w-full rounded-xl border-gray-200"></div>
<div><label class="block font-bold mb-2">Description</label><textarea name="description" rows="5" required class="w-full rounded-xl border-gray-200">{{ old('description') }}</textarea></div>
<div class="grid sm:grid-cols-2 gap-4"><div><label class="block font-bold mb-2">Price (IDR)</label><input type="number" name="price" min="0" step="1000" value="{{ old('price',0) }}" required class="w-full rounded-xl border-gray-200"></div><div><label class="block font-bold mb-2">Available quantity</label><input type="number" name="stock" min="1" value="{{ old('stock',100) }}" required class="w-full rounded-xl border-gray-200"></div></div>
<div><label class="block font-bold mb-2">Category</label><select name="category_id" class="w-full rounded-xl border-gray-200"><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
<div><label class="block font-bold mb-2">Preview image (optional, max 5 MB)</label><input type="file" name="preview" accept="image/*" class="w-full text-sm"></div>
<div><label class="block font-bold mb-2">Digital product file (required, max 50 MB)</label><input type="file" name="digital_file" required class="w-full text-sm"><p class="text-xs text-gray-500 mt-1">File is stored privately and only paid customers can download it.</p></div>
<button class="rounded-full bg-[#9060d4] text-white px-7 py-3 font-bold">Publish product</button>
</form></div>
@endsection
