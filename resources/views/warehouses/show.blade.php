
@extends('layouts.app')

@section('title', 'Detail Gudang - RESQNET')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('warehouses.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali ke Data Gudang
            </a>
            <h2 class="fw-bold mt-3 mb-1">Detail Gudang</h2>
            <p class="text-muted mb-0">
                Informasi gudang dan persediaan barang bantuan.
            </p>
        </div>

        <a href="{{ route('warehouses.edit', $warehouse->id) }}"
           class="btn btn-warning">
            <i class="bi bi-pencil-square me-1"></i> Edit Gudang
        </a>
    </div>

    @php
        $stocks = $warehouse->stocks;

        $totalItems = $stocks->count();

        $availableItems = $stocks->filter(function ($stock) {
            return ($stock->quantity - $stock->reserved_quantity) > 0;
        })->count();

        $emptyItems = $stocks->filter(function ($stock) {
            return ($stock->quantity - $stock->reserved_quantity) <= 0;
        })->count();
    @endphp

    {{-- Informasi Gudang --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-2">{{ $warehouse->name }}</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ $warehouse->address ?: 'Alamat belum tersedia' }}
                    </p>
                </div>

                @if($warehouse->is_active)
                    <span class="badge bg-success px-3 py-2">Aktif</span>
                @else
                    <span class="badge bg-secondary px-3 py-2">Tidak Aktif</span>
                @endif
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Nama Pengelola</div>
                    <div class="fw-semibold">{{ $warehouse->manager_name ?: '-' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small mb-1">Nomor Telepon</div>
                    <div class="fw-semibold">{{ $warehouse->phone ?: '-' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small mb-1">Kecamatan</div>
                    <div class="fw-semibold">{{ $warehouse->district ?: '-' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small mb-1">Kota/Kabupaten</div>
                    <div class="fw-semibold">{{ $warehouse->city ?: '-' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small mb-1">Provinsi</div>
                    <div class="fw-semibold">{{ $warehouse->province ?: '-' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small mb-1">Koordinat Lokasi</div>
                    <div class="fw-semibold">
                        @if($warehouse->latitude !== null && $warehouse->longitude !== null)
                            {{ $warehouse->latitude }}, {{ $warehouse->longitude }}
                        @else
                            Belum tersedia
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Inventaris --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Jenis Barang</div>
                    <h3 class="fw-bold mt-2 mb-0">
                        {{ number_format($totalItems) }}
                    </h3>
                    <small class="text-muted">Jenis barang terdaftar di gudang</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Barang Tersedia</div>
                    <h3 class="fw-bold text-success mt-2 mb-0">
                        {{ number_format($availableItems) }}
                    </h3>
                    <small class="text-muted">Jenis barang dengan stok tersedia</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Barang Habis</div>
                    <h3 class="fw-bold text-danger mt-2 mb-0">
                        {{ number_format($emptyItems) }}
                    </h3>
                    <small class="text-muted">Jenis barang tanpa stok tersedia</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Persediaan --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Persediaan Barang</h5>
            <p class="text-muted small mb-4">
                Rincian jumlah dan ketersediaan setiap barang di gudang.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Total Stok</th>
                            <th>Dialokasikan</th>
                            <th>Tersedia</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($stocks as $stock)
                            @php
                                $available = max(
                                    0,
                                    $stock->quantity - $stock->reserved_quantity
                                );
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-semibold">
                                    {{ $stock->aidItem->name ?? 'Barang tidak ditemukan' }}
                                </td>

                                <td>{{ $stock->aidItem->category ?? '-' }}</td>

                                <td>{{ $stock->aidItem->unit ?? '-' }}</td>

                                <td>{{ number_format($stock->quantity) }}</td>

                                <td>
                                    <span class="text-warning fw-semibold">
                                        {{ number_format($stock->reserved_quantity) }}
                                    </span>
                                </td>

                                <td>
                                    @if($available > 0)
                                        <span class="badge bg-success">
                                            {{ number_format($available) }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                                    Belum ada persediaan barang di gudang ini.
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
