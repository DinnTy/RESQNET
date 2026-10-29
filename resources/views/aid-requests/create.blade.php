
@extends('layouts.app')

@section('title', 'Ajukan Bantuan - RESQNET')

@section('content')
<div class="container py-4">

    <div class="mb-4">
        <h3 class="fw-bold">Ajukan Kebutuhan Bantuan</h3>
        <p class="text-muted mb-0">
            Catat seluruh kebutuhan bantuan berdasarkan kondisi di lokasi bencana.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali formulir:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            @if($reports->count() > 0)

                <form action="{{ route('aid-requests.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Laporan Bencana</label>

                        <select name="disaster_report_id" class="form-select" required>
                            <option value="">Pilih laporan bencana</option>

                            @foreach($reports as $report)
                                <option value="{{ $report->id }}"
                                    {{ old('disaster_report_id', $selectedReportId) == $report->id ? 'selected' : '' }}>
                                    {{ $report->title }}
                                    — {{ $report->location->name ?? 'Lokasi tidak tersedia' }}
                                </option>
                            @endforeach
                        </select>

                        <small class="text-muted">
                            Hanya laporan yang sudah terverifikasi yang dapat dipilih.
                        </small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Daftar Kebutuhan Barang</h5>
                            <small class="text-muted">
                                Tambahkan semua barang yang dibutuhkan dalam satu pengajuan.
                            </small>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm"
                                id="addItem">
                            <i class="bi bi-plus-lg"></i> Tambah Barang
                        </button>
                    </div>

                    <div id="itemsContainer">

                        @php
                            $oldItems = old('items', [
                                ['aid_item_id' => '', 'quantity' => '', 'unit' => 'unit', 'priority' => 'sedang']
                            ]);
                        @endphp

                        @foreach($oldItems as $index => $oldItem)
                            <div class="item-row border rounded-3 p-3 mb-3">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <strong class="item-number">Barang {{ $index + 1 }}</strong>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger remove-item">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nama Barang</label>

                                    <select name="items[{{ $index }}][aid_item_id]"
                                            class="form-select" required>
                                        <option value="">Pilih barang bantuan</option>

                                        @foreach($aidItems as $item)
                                            <option value="{{ $item->id }}"
                                                {{ ($oldItem['aid_item_id'] ?? '') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Jumlah</label>
                                        <input type="number"
                                               name="items[{{ $index }}][quantity]"
                                               class="form-control"
                                               min="1"
                                               value="{{ $oldItem['quantity'] ?? '' }}"
                                               placeholder="Contoh: 350"
                                               required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Satuan</label>
                                        <select name="items[{{ $index }}][unit]"
                                                class="form-select" required>
                                            @foreach(['unit' => 'Unit', 'dus' => 'Dus', 'pak' => 'Pak', 'kg' => 'Kilogram', 'liter' => 'Liter', 'lembar' => 'Lembar', 'buah' => 'Buah'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ ($oldItem['unit'] ?? 'unit') === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Prioritas</label>
                                        <select name="items[{{ $index }}][priority]"
                                                class="form-select" required>
                                            @foreach(['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi', 'kritis' => 'Kritis'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ ($oldItem['priority'] ?? 'sedang') === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keterangan Tambahan</label>
                        <textarea name="notes"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Jelaskan kondisi dan kebutuhan bantuan secara lebih rinci...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Kirim Permintaan
                        </button>

                        <a href="{{ route('aid-requests.index') }}"
                           class="btn btn-outline-secondary">
                            Kembali
                        </a>
                    </div>

                </form>

            @else
                <div class="text-center py-4">
                    <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                    <h5 class="fw-bold mt-3">Belum Ada Laporan Terverifikasi</h5>
                    <p class="text-muted">
                        Permintaan bantuan hanya dapat dibuat berdasarkan laporan
                        yang sudah diverifikasi.
                    </p>
                    <a href="{{ route('reports.index') }}" class="btn btn-primary">
                        Lihat Laporan Bencana
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>

@if($reports->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('itemsContainer');
    const addButton = document.getElementById('addItem');

    function updateNumbers() {
        container.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelector('.item-number').textContent = 'Barang ' + (index + 1);
        });
    }

    addButton.addEventListener('click', function () {
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('select, input').forEach(field => {
            field.value = field.tagName === 'SELECT'
                ? ''
                : '';
        });

        newRow.querySelectorAll('select, input').forEach(field => {
            field.name = field.name.replace(/items\[\d+\]/, 'items[NEW]');
        });

        container.appendChild(newRow);
        updateNames();
        updateNumbers();
    });

    function updateNames() {
        container.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelectorAll('select, input').forEach(field => {
                field.name = field.name.replace(/items\[(.*?)\]/, 'items[' + index + ']');
            });
        });
    }

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-item');

        if (!button) return;

        const rows = container.querySelectorAll('.item-row');

        if (rows.length <= 1) {
            alert('Minimal harus ada satu jenis barang.');
            return;
        }

        button.closest('.item-row').remove();
        updateNames();
        updateNumbers();
    });
});
</script>
@endif
@endsection
