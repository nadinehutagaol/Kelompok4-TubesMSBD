@extends('admin.layouts.app')

@section('content')

<h1 class="h3 mb-4 text-gray-800">Manajemen Shots</h1>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Daftar Karya Desain
        </h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Desainer</th>
                        <th>Likes</th>
                        <th>Komentar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($shots as $shot)
                    <tr>
                        <td>{{ $shot->id }}</td>
                        <td>{{ $shot->title }}</td>
                        <td>{{ $shot->user->full_name ?? '-' }}</td>
                        <td>{{ $shot->likes_count }}</td>
                        <td>{{ $shot->comments_count }}</td>

                        <td class="text-center">
                            <a href="{{ route('admin.shots.show', $shot) }}"
                               class="btn btn-sm btn-info"
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>

                            <form action="{{ route('admin.shots.destroy', $shot) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus karya ini beserta data terkait?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-sm btn-danger"
                                        title="Hapus Shot">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            Belum ada karya desain.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $shots->links() }}
        </div>
    </div>
</div>

@endsection
