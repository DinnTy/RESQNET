@extends('layouts.app')

@section('title', 'Detail Barang Bantuan - RESQNET')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('aid-items.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali ke Data Barang
            </a>
            <h2 class="fw-bold mt-3 mb-1">Detail Barang Bantuan</h2>
            <p class="text-muted mb-0">Informasi barang dan persebaran stok di gudang.</p>
        </div>

        <a href="{{ route('aid-items.edit', $aidItem->id) }}"
           class="btn btn-warning">
            <i class="bi bi-pencil-square me-1"></i> Edit Barang
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Nama Barang</div>
                    <h4 class="fw-bold">{{ $aidItem->name }}</h4>
                    <span class="badge bg-primary">
                        {{ $aidItem->category ?? 'Tanpa Kategori' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Satuan Barang</div>
                    <h4 class="fw-bold">{{ $aidItem->unit }}</h4>
                    <span class="text-muted small">Satuan inventaris</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Total Stok</div>
                    <h4 class="fw-bold text-primary">
                        {{ number_format($aidItem->stocks->sum('quantity')) }}
                    </h4>
                    <span class="text-muted small">Seluruh gudang</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Deskripsi Barang</h5>
            <p class="text-muted mb-0">
                {{ $aidItem->description ?: 'Belum ada deskripsi barang.' }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Stok Berdasarkan Gudang</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Nama Gudang</th>
                            <th>Total Stok</th>
                            <th>Stok Dipesan</th>
                            <th>Stok Tersedia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aidItem->stocks as $stock)
                            @php
                                $available = max(
                                    0,
                                    $stock->quantity - $stock->reserved_quantity
                                );
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">
                                    {{ $stock->warehouse->name ?? 'Gudang tidak ditemukan' }}
                                </td>
                                <td>{{ number_format($stock->quantity) }}</td>
                                <td>{{ number_format($stock->reserved_quantity) }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ number_format($available) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada stok barang di gudang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection