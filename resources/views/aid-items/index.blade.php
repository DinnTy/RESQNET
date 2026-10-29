
@extends('layouts.app')

@section('title', 'Data Barang Bantuan - RESQNET')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Data Barang Bantuan</h2>
            <p class="text-muted mb-0">
                Kelola katalog barang bantuan RESQNET.
            </p>
        </div>

        <a href="{{ route('aid-items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Tambah Barang
        </a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Tabel Barang --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-1">Daftar Barang Bantuan</h5>
            <p class="text-muted small mb-0">
                Seluruh barang yang terdaftar dalam sistem.
            </p>
        </div>

        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Total Stok</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($aidItems as $item)
                            <tr>
                                <td>
                                    {{ $aidItems->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $item->name }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $item->category ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>

                                <td>{{ $item->unit }}</td>

                                <td>
                                    <span class="fw-semibold text-primary">
                                        {{ number_format($item->stocks_sum_quantity ?? 0) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        {{-- Detail --}}
                                        <a href="{{ route('aid-items.show', $item->id) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>

                                        {{-- Edit --}}
                                        <a href="{{ route('aid-items.edit', $item->id) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit Barang">
                                            <i class="bi bi-pencil-square"></i>
                                            Edit
                                        </a>

                                        {{-- Hapus --}}
                                        <form action="{{ route('aid-items.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus barang {{ addslashes($item->name) }}?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Hapus Barang">
                                                <i class="bi bi-trash"></i>
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada data barang bantuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $aidItems->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
