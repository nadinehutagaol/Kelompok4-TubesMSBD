@extends('admin.layouts.app')

@section('content')

<h1 class="h3 mb-4 text-gray-800">Manajemen Jobs</h1>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Daftar Lowongan Pekerjaan
        </h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Posisi</th>
                        <th>Perusahaan</th>
                        <th>Lokasi</th>
                        <th>Tipe Pekerjaan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($jobs as $job)
                    <tr>
                        <td>{{ $job->id }}</td>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->company_name ?? '-' }}</td>
                        <td>{{ $job->location ?? '-' }}</td>
                        <td>{{ $job->job_type ?? '-' }}</td>

                        <td>
                            <details class="mb-2">
                                <summary class="text-primary"
                                         style="cursor: pointer;">
                                    Lihat Detail
                                </summary>

                                <div class="mt-2">
                                    {{ $job->description ?? strip_tags($job->description_html ?? '') }}
                                </div>
                            </details>

                            <form action="{{ route('admin.jobs.destroy', $job) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus lowongan ini?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            Belum ada lowongan pekerjaan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $jobs->links() }}
        </div>
    </div>
</div>

@endsection
