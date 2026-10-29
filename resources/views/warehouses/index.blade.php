
@extends('layouts.app')

@section('title', 'Manajemen Gudang - RESQNET')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manajemen Gudang</h2>
            <p class="text-muted mb-0">
                Kelola lokasi penyimpanan bantuan RESQNET.
            </p>
        </div>

        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Gudang
        </a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Gudang</div>
                    <h3 class="fw-bold mb-0">{{ $warehouses->total() }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Jumlah Data Stok</div>
                    <h3 class="fw-bold mb-0">
                        {{ $warehouses->sum('stocks_count') }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Gudang --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-1">Daftar Gudang</h5>
            <p class="text-muted small mb-0">
                Informasi lokasi dan pengelolaan gudang bantuan.
            </p>
        </div>

        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Nama Gudang</th>
                            <th>Pengelola</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th>Jumlah Stok</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td>
                                    {{ $warehouses->firstItem() + $loop->index }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $warehouse->name }}
                                </td>

                                <td>{{ $warehouse->manager_name ?? '-' }}</td>

                                <td>{{ $warehouse->address ?? '-' }}</td>

                                <td>{{ $warehouse->phone ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $warehouse->stocks_count }} jenis stok
                                    </span>
                                </td>

                                <td>
                                    @if($warehouse->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        <a href="{{ route('warehouses.show', $warehouse->id) }}"
                                           class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>

                                        <a href="{{ route('warehouses.edit', $warehouse->id) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        <form action="{{ route('warehouses.destroy', $warehouse->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus gudang ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-building fs-2 d-block mb-2"></i>
                                    Belum ada data gudang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $warehouses->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
