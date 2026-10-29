
@extends('layouts.app')

@section('title', 'Data Stok Bantuan - RESQNET')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Data Stok Bantuan</h2>
            <p class="text-muted mb-0">
                Kelola persediaan barang bantuan di setiap gudang.
            </p>
        </div>

        <a href="{{ route('aid-stocks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Stok
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Tabel Stok --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-bold mb-0">Daftar Persediaan</h5>
                <span class="text-muted small">
                    {{ $stocks->total() }} data stok
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Gudang</th>
                            <th>Barang Bantuan</th>
                            <th>Satuan</th>
                            <th>Total Stok</th>
                            <th>Dialokasikan</th>
                            <th>Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($stocks as $stock)
                            @php
                                $available = max(
                                    0,
                                    $stock->quantity - $stock->reserved_quantity
                                );
                            @endphp

                            <tr>
                                <td>{{ $stocks->firstItem() + $loop->index }}</td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $stock->warehouse->name ?? '-' }}
                                    </div>
                                </td>

                                <td class="fw-semibold">
                                    {{ $stock->aidItem->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $stock->aidItem->unit ?? '-' }}
                                </td>

                                <td>
                                    {{ number_format($stock->quantity) }}
                                </td>

                                <td>
                                    <span class="text-warning fw-semibold">
                                        {{ number_format($stock->reserved_quantity) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($available > 0)
                                        <span class="badge bg-success">
                                            {{ number_format($available) }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">0</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('aid-stocks.show', $stock->id) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('aid-stocks.edit', $stock->id) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form action="{{ route('aid-stocks.destroy', $stock->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus stok ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                                    Belum ada data stok bantuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $stocks->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
