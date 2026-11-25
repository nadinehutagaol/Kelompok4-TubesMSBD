
@extends('admin.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Detail Shot</h1>

    <a href="{{ route('admin.shots.index') }}"
       class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-body">

        <h3 class="font-weight-bold">
            {{ $shot->title }}
        </h3>

        <p class="text-muted">
            Desainer: {{ $shot->user->full_name ?? '-' }}
        </p>

        @if($shot->image_url)
            <img
                src="{{ \Illuminate\Support\Str::startsWith($shot->image_url, ['http://', 'https://']) ? $shot->image_url : asset('storage/' . $shot->image_url) }}"
                class="img-fluid rounded mb-3"
                style="max-height: 450px; object-fit: contain;"
                alt="{{ $shot->title }}">
        @endif

        <p>{{ $shot->description }}</p>

        <div class="mb-3">
            @foreach($shot->categories as $category)
                <span class="badge badge-secondary">
                    {{ $category->name }}
                </span>
            @endforeach
        </div>

        <p>
            <i class="fas fa-heart text-danger"></i>
            {{ $shot->likes_count }} Likes

            &nbsp;&nbsp;

            <i class="fas fa-comment"></i>
            {{ $shot->comments_count }} Komentar
        </p>

        <form action="{{ route('admin.shots.destroy', $shot) }}"
              method="POST"
              onsubmit="return confirm('Yakin ingin menghapus karya ini beserta data terkait?');">
            @csrf
            @method('DELETE')

            <button class="btn btn-danger" type="submit">
                <i class="fas fa-trash"></i> Hapus Shot
            </button>
        </form>

    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Moderasi Komentar
        </h6>
    </div>

    <div class="card-body">

        @forelse($shot->comments as $comment)

            <div class="border-bottom pb-3 mb-3">

                <div class="d-flex justify-content-between">
                    <strong>
                        {{ $comment->user->full_name ?? 'Pengguna' }}
                    </strong>

                    <small class="text-muted">
                        {{ $comment->created_at }}
                    </small>
                </div>

                <p class="mt-2">{{ $comment->body }}</p>

                <form action="{{ route('admin.comments.destroy', $comment) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus komentar ini?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                        Hapus Komentar
                    </button>
                </form>

            </div>

        @empty

            <p class="text-muted">Belum ada komentar.</p>

        @endforelse

    </div>
</div>

@endsection