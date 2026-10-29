
@extends('layouts.app')

@section('title', 'Edit Stok Bantuan - RESQNET')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('aid-stocks.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Kembali ke Data Stok
        </a>

        <h2 class="fw-bold mt-3 mb-1">Edit Stok Bantuan</h2>
        <p class="text-muted mb-0">
            Perbarui jumlah stok bantuan pada gudang.
        </p>
    </div>

    {{-- Validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Periksa kembali data yang dimasukkan.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $reserved = $aidStock->reserved_quantity;
        $quantity = old('quantity', $aidStock->quantity);
        $available = max(0, $quantity - $reserved);
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">Informasi Persediaan</h5>

            <form action="{{ route('aid-stocks.update', $aidStock->id) }}"
                  method="POST">
                @csrf
                @method('PUT')

                {{-- Gudang --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Gudang</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="{{ $aidStock->warehouse->name ?? '-' }}"
                           readonly>
                </div>

                {{-- Barang --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Barang Bantuan</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="{{ $aidStock->aidItem->name ?? '-' }}"
                           readonly>
                </div>

                {{-- Satuan --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Satuan</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="{{ $aidStock->aidItem->unit ?? '-' }}"
                           readonly>
                </div>

                {{-- Stok Dialokasikan --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Stok Dialokasikan
                    </label>
                    <input type="number"
                           class="form-control bg-light"
                           value="{{ $reserved }}"
                           readonly>

                    <small class="text-muted">
                        Stok yang sudah dipesan tidak dapat diubah melalui form ini.
                    </small>
                </div>

                {{-- Total Stok --}}
                <div class="mb-3">
                    <label for="quantity" class="form-label fw-semibold">
                        Total Stok
                    </label>

                    <input type="number"
                           name="quantity"
                           id="quantity"
                           class="form-control"
                           min="{{ $reserved }}"
                           value="{{ $quantity }}"
                           required>

                    <small class="text-muted">
                        Total stok tidak boleh lebih kecil dari
                        {{ number_format($reserved) }}
                        {{ $aidStock->aidItem->unit ?? '' }}
                        yang sudah dialokasikan.
                    </small>
                </div>

                {{-- Preview Stok Tersedia --}}
                <div class="p-3 rounded bg-light mb-4">
                    <div class="text-muted small">Perkiraan Stok Tersedia</div>
                    <h4 class="fw-bold text-success mb-0 mt-1"
                        id="availablePreview">
                        {{ number_format($available) }}
                        {{ $aidStock->aidItem->unit ?? '' }}
                    </h4>
                </div>

                {{-- Tombol --}}
                <div class="d-flex justify-content-end flex-wrap gap-2">
                    <a href="{{ route('aid-stocks.index') }}"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

{{-- Preview otomatis --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantityInput = document.getElementById('quantity');
    const preview = document.getElementById('availablePreview');
    const reserved = {{ (int) $reserved }};
    const unit = @json($aidStock->aidItem->unit ?? '');

    function updatePreview() {
        const quantity = parseInt(quantityInput.value) || 0;
        const available = Math.max(0, quantity - reserved);

        preview.textContent =
            available.toLocaleString('id-ID') +
            (unit ? ' ' + unit : '');
    }

    quantityInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>
@endsection
