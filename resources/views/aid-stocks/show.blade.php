
@extends('layouts.app')

@section('title', 'Detail Stok Bantuan - RESQNET')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('aid-stocks.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Kembali ke Data Stok
        </a>

        <h2 class="fw-bold mt-3 mb-1">Detail Stok Bantuan</h2>
        <p class="text-muted mb-0">
            Informasi persediaan barang bantuan di gudang.
        </p>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @php
        $available = max(
            0,
            $aidStock->quantity - $aidStock->reserved_quantity
        );
    @endphp

    {{-- Informasi Stok --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">Informasi Persediaan</h5>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="text-muted small mb-1">Nama Gudang</div>
                    <div class="fw-semibold">
                        {{ $aidStock->warehouse->name ?? '-' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small mb-1">Nama Barang</div>
                    <div class="fw-semibold">
                        {{ $aidStock->aidItem->name ?? '-' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small mb-1">Kategori</div>
                    <div class="fw-semibold">
                        {{ $aidStock->aidItem->category ?? '-' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small mb-1">Satuan</div>
                    <div class="fw-semibold">
                        {{ $aidStock->aidItem->unit ?? '-' }}
                    </div>
                </div>

            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Ringkasan Stok</h6>

            <div class="row g-3">

                <div class="col-md-4">
                    <div class="p-3 rounded bg-light h-100">
                        <div class="text-muted small">Total Stok</div>
                        <h3 class="fw-bold mt-2 mb-0">
                            {{ number_format($aidStock->quantity) }}
                        </h3>
                        <small class="text-muted">
                            {{ $aidStock->aidItem->unit ?? '' }}
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded bg-light h-100">
                        <div class="text-muted small">Dialokasikan</div>
                        <h3 class="fw-bold text-warning mt-2 mb-0">
                            {{ number_format($aidStock->reserved_quantity) }}
                        </h3>
                        <small class="text-muted">
                            {{ $aidStock->aidItem->unit ?? '' }}
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded bg-light h-100">
                        <div class="text-muted small">Stok Tersedia</div>
                        <h3 class="fw-bold text-success mt-2 mb-0">
                            {{ number_format($available) }}
                        </h3>
                        <small class="text-muted">
                            {{ $aidStock->aidItem->unit ?? '' }}
                        </small>
                    </div>
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('aid-stocks.edit', $aidStock->id) }}"
                   class="btn btn-warning">
                    <i class="bi bi-pencil-square me-1"></i>
                    Edit Stok
                </a>

                <a href="{{ route('aid-stocks.index') }}"
                   class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </div>
    </div>

</div>
@endsection
