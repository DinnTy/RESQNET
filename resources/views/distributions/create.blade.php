@extends('layouts.app')

@section('title', 'Buat Distribusi Bantuan - RESQNET')

@section('content')
<div class="container py-3">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">Buat Distribusi Bantuan</h2>
        <p class="text-muted">
            Alokasikan beberapa jenis bantuan dari satu atau beberapa gudang
            berdasarkan jarak geografis dan ketersediaan stok.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('distributions.store') }}"
          method="POST"
          id="distribution-form">
        @csrf

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <strong>1. Pilih Permintaan Bantuan</strong>
            </div>

            <div class="card-body">
                <label for="aid_request_id" class="form-label">
                    Permintaan Bantuan
                </label>

                <select name="aid_request_id"
                        id="aid_request_id"
                        class="form-select"
                        required>
                    <option value="">Pilih Permintaan Bantuan</option>

                    @foreach ($aidRequests as $item)
                        <option value="{{ $item->id }}"
                            {{ old('aid_request_id') == $item->id ? 'selected' : '' }}>
                            Permintaan #{{ $item->id }}
                            - {{ $item->disasterReport?->title ?? 'Laporan bencana' }}
                            ({{ ucfirst($item->status) }})
                        </option>
                    @endforeach
                </select>

                <div id="request-info"
                     class="alert alert-info mt-3"
                     style="display:none;">
                    <strong>Informasi permintaan</strong>
                    <div id="request-location" class="mt-1"></div>
                    <div id="request-status"></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <strong>2. Daftar Kebutuhan Barang</strong>
            </div>

            <div class="card-body">
                <div id="items-container">
                    <p class="text-muted mb-0">
                        Pilih permintaan bantuan terlebih dahulu.
                    </p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <strong>3. Rekomendasi Gudang</strong>
            </div>

            <div class="card-body">
                <p class="text-muted">
                    Sistem mengurutkan gudang berdasarkan jarak terdekat
                    dan stok yang tersedia untuk setiap jenis barang.
                </p>

                <div id="matching-container">
                    <div class="alert alert-secondary mb-0">
                        Pilih permintaan bantuan untuk melihat rekomendasi.
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <strong>4. Alokasi Multi-Barang dan Multi-Gudang</strong>
            </div>

            <div class="card-body">
                <div id="allocation-container">
                    <p class="text-muted mb-0">
                        Belum ada alokasi barang.
                    </p>
                </div>

                <div id="allocation-summary"
                     class="alert alert-info mt-3"
                     style="display:none;">
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <strong>5. Catatan Distribusi</strong>
            </div>

            <div class="card-body">
                <textarea name="notes"
                          class="form-control"
                          rows="3"
                          placeholder="Masukkan catatan distribusi...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <button type="submit"
                class="btn btn-primary"
                id="submit-button"
                disabled>
            <i class="bi bi-box-seam"></i>
            Simpan Distribusi
        </button>

        <a href="{{ route('distributions.index') }}"
           class="btn btn-secondary">
            Kembali
        </a>

    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const requests = @json($requestsData);
    const stocks = @json($stocks);

    const requestSelect = document.getElementById('aid_request_id');
    const itemsContainer = document.getElementById('items-container');
    const matchingContainer = document.getElementById('matching-container');
    const allocationContainer = document.getElementById('allocation-container');
    const allocationSummary = document.getElementById('allocation-summary');
    const submitButton = document.getElementById('submit-button');

    let currentRequest = null;
    let selectedAllocations = {};

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function getItem(itemId) {
        if (!currentRequest) return null;

        return currentRequest.items.find(item =>
            Number(item.aid_item_id) === Number(itemId)
        );
    }

    function getAllocations(itemId) {
        return selectedAllocations[itemId] || {};
    }

    function getAllocatedTotal(itemId) {
        return Object.values(getAllocations(itemId))
            .reduce((total, quantity) => total + Number(quantity || 0), 0);
    }

    function getRemaining(itemId) {
        const item = getItem(itemId);

        if (!item) return 0;

        return Math.max(
            0,
            Number(item.remaining_need) - getAllocatedTotal(itemId)
        );
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;

        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;

        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(lat1 * Math.PI / 180) *
            Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) ** 2;

        return 2 * R * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );
    }

    function distanceToWarehouse(stock) {
        const values = [
            currentRequest?.latitude,
            currentRequest?.longitude,
            stock.warehouse_latitude,
            stock.warehouse_longitude
        ];

        if (values.some(value =>
            value === null ||
            value === undefined ||
            value === '' ||
            !Number.isFinite(Number(value))
        )) {
            return Infinity;
        }

        const lat1 = Number(currentRequest.latitude);
        const lon1 = Number(currentRequest.longitude);
        const lat2 = Number(stock.warehouse_latitude);
        const lon2 = Number(stock.warehouse_longitude);

        if (
            Math.abs(lat1) > 90 ||
            Math.abs(lat2) > 90 ||
            Math.abs(lon1) > 180 ||
            Math.abs(lon2) > 180
        ) {
            return Infinity;
        }

        return calculateDistance(lat1, lon1, lat2, lon2);
    }

    function getMatchingStocks(itemId) {
        return stocks
            .filter(stock =>
                Number(stock.aid_item_id) === Number(itemId) &&
                Number(stock.available) > 0
            )
            .map(stock => ({
                ...stock,
                distance: distanceToWarehouse(stock)
            }))
            .sort((a, b) => {
                if (a.distance === Infinity && b.distance !== Infinity) return 1;
                if (a.distance !== Infinity && b.distance === Infinity) return -1;

                if (a.distance !== b.distance) {
                    return a.distance - b.distance;
                }

                return Number(b.available) - Number(a.available);
            });
    }

    function renderItems() {
        if (!currentRequest) return;

        if (!currentRequest.items.length) {
            itemsContainer.innerHTML =
                '<div class="alert alert-warning mb-0">Tidak ada barang yang dapat diproses pada permintaan ini.</div>';
            return;
        }

        itemsContainer.innerHTML = currentRequest.items.map((item, index) => {
            const allocated = getAllocatedTotal(item.aid_item_id);
            const remaining = getRemaining(item.aid_item_id);

            return `
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-1">
                                ${index + 1}. ${escapeHtml(item.name)}
                            </h6>
                            <small class="text-muted">
                                Prioritas: ${escapeHtml(item.priority)}
                            </small>
                        </div>
                        <span class="badge bg-primary align-self-start">
                            ${escapeHtml(item.unit)}
                        </span>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <small class="text-muted">Kebutuhan awal</small>
                            <div class="fw-semibold">
                                ${item.quantity} ${escapeHtml(item.unit)}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Sudah dialokasikan</small>
                            <div class="fw-semibold">
                                ${item.already_allocated} ${escapeHtml(item.unit)}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Sisa kebutuhan</small>
                            <div class="fw-semibold text-primary">
                                ${remaining} ${escapeHtml(item.unit)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderMatching() {
        if (!currentRequest) return;

        const items = currentRequest.items.filter(item =>
            Number(item.remaining_need) > 0
        );

        if (!items.length) {
            matchingContainer.innerHTML =
                '<div class="alert alert-info">Semua kebutuhan pada permintaan ini sudah dialokasikan.</div>';
            return;
        }

        matchingContainer.innerHTML = items.map(item => {
            const matchingStocks = getMatchingStocks(item.aid_item_id);
            const remaining = getRemaining(item.aid_item_id);

            if (!matchingStocks.length) {
                return `
                    <div class="border rounded p-3 mb-3">
                        <h6 class="fw-bold">${escapeHtml(item.name)}</h6>
                        <div class="alert alert-warning mb-0">
                            Belum ada stok tersedia untuk barang ini.
                        </div>
                    </div>
                `;
            }

            let recommendedRemaining = remaining;

            const rows = matchingStocks.map((stock, index) => {
                const selected = getAllocations(item.aid_item_id);
                const selectedQuantity = Number(selected[stock.warehouse_id] || 0);

                const recommended = selectedQuantity > 0
                    ? selectedQuantity
                    : Math.min(Number(stock.available), recommendedRemaining);

                if (selectedQuantity === 0) {
                    recommendedRemaining -= recommended;
                }

                const distanceLabel = Number.isFinite(stock.distance)
                    ? stock.distance.toFixed(2) + ' km'
                    : 'Koordinat tidak tersedia';

                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <strong>${escapeHtml(stock.warehouse_name)}</strong>
                            <br>
                            <small class="text-muted">${distanceLabel}</small>
                            ${index === 0 && Number.isFinite(stock.distance)
                                ? '<br><span class="badge bg-success">Prioritas terdekat</span>'
                                : ''}
                        </td>
                        <td>
                            ${stock.available} ${escapeHtml(item.unit)}
                        </td>
                        <td>
                            ${recommended} ${escapeHtml(item.unit)}
                        </td>
                        <td>
                            <button type="button"
                                    class="btn btn-sm ${selectedQuantity > 0 ? 'btn-success' : 'btn-outline-primary'} select-warehouse"
                                    data-item="${item.aid_item_id}"
                                    data-warehouse="${stock.warehouse_id}"
                                    ${selectedQuantity > 0 || remaining <= 0 ? 'disabled' : ''}>
                                ${selectedQuantity > 0 ? 'Dipilih' : 'Pilih'}
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            const totalAvailable = matchingStocks.reduce(
                (total, stock) => total + Number(stock.available), 0
            );

            const message = totalAvailable < remaining
                ? `<div class="alert alert-warning mb-2">Stok tersedia ${totalAvailable} ${escapeHtml(item.unit)}. Kekurangan ${remaining - totalAvailable} ${escapeHtml(item.unit)}.</div>`
                : `<div class="alert alert-success mb-2">Stok tersedia mencukupi sisa kebutuhan barang ini.</div>`;

            return `
                <div class="border rounded p-3 mb-4">
                    <h6 class="fw-bold mb-3">
                        ${escapeHtml(item.name)}
                        <span class="text-muted fw-normal">
                            (Sisa ${remaining} ${escapeHtml(item.unit)})
                        </span>
                    </h6>

                    ${message}

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Gudang dan Jarak</th>
                                    <th>Stok Tersedia</th>
                                    <th>Rekomendasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderAllocations() {
        if (!currentRequest) return;

        let html = '';
        let hasAllocation = false;

        currentRequest.items.forEach(item => {
            const allocations = getAllocations(item.aid_item_id);
            const warehouseIds = Object.keys(allocations);

            if (!warehouseIds.length) return;

            hasAllocation = true;

            html += `
                <div class="border rounded p-3 mb-3">
                    <h6 class="fw-bold mb-3">${escapeHtml(item.name)}</h6>
            `;

            warehouseIds.forEach(warehouseId => {
                const stock = stocks.find(s =>
                    Number(s.warehouse_id) === Number(warehouseId) &&
                    Number(s.aid_item_id) === Number(item.aid_item_id)
                );

                if (!stock) return;

                const quantity = Number(allocations[warehouseId]);

                html += `
                    <div class="row align-items-end mb-3">
                        <div class="col-md-5 mb-2">
                            <label class="form-label">Gudang</label>
                            <input type="text"
                                   class="form-control"
                                   value="${escapeHtml(stock.warehouse_name)}"
                                   readonly>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label">
                                Jumlah (${escapeHtml(item.unit)})
                            </label>
                            <input type="number"
                                   name="allocations[${item.aid_item_id}][${warehouseId}]"
                                   class="form-control allocation-quantity"
                                   data-item="${item.aid_item_id}"
                                   data-warehouse="${warehouseId}"
                                   min="1"
                                   max="${stock.available}"
                                   value="${quantity}"
                                   required>
                            <small class="text-muted">
                                Stok tersedia: ${stock.available} ${escapeHtml(item.unit)}
                            </small>
                        </div>

                        <div class="col-md-3 mb-2">
                            <button type="button"
                                    class="btn btn-danger remove-allocation"
                                    data-item="${item.aid_item_id}"
                                    data-warehouse="${warehouseId}">
                                Hapus
                            </button>
                        </div>
                    </div>
                `;
            });

            html += `
                    <div class="text-end">
                        <strong>
                            Total alokasi: ${getAllocatedTotal(item.aid_item_id)}
                            ${escapeHtml(item.unit)}
                        </strong>
                        <br>
                        <small class="text-muted">
                            Sisa setelah alokasi: ${getRemaining(item.aid_item_id)}
                            ${escapeHtml(item.unit)}
                        </small>
                    </div>
                </div>
            `;
        });

        allocationContainer.innerHTML = hasAllocation
            ? html
            : '<p class="text-muted mb-0">Belum ada gudang yang dipilih.</p>';

        updateSummary();
    }

    function updateSummary() {
        if (!currentRequest) return;

        const rows = currentRequest.items.map(item => {
            const total = getAllocatedTotal(item.aid_item_id);
            const remaining = getRemaining(item.aid_item_id);

            return `
                <div class="d-flex justify-content-between flex-wrap">
                    <span>${escapeHtml(item.name)}</span>
                    <strong>${total} / ${item.remaining_need} ${escapeHtml(item.unit)}</strong>
                </div>
                <small class="text-muted">Sisa: ${remaining} ${escapeHtml(item.unit)}</small>
                <hr>
            `;
        }).join('');

        allocationSummary.style.display = 'block';
        allocationSummary.innerHTML = '<strong>Ringkasan alokasi</strong>' + rows;

        validateForm();
    }

    function validateForm() {
        if (!currentRequest) {
            submitButton.disabled = true;
            return;
        }

        let hasAllocation = false;
        let invalid = false;

        currentRequest.items.forEach(item => {
            const allocations = getAllocations(item.aid_item_id);
            const total = getAllocatedTotal(item.aid_item_id);

            if (total > 0) hasAllocation = true;

            if (total > Number(item.remaining_need)) {
                invalid = true;
            }

            Object.entries(allocations).forEach(([warehouseId, quantity]) => {
                const stock = stocks.find(s =>
                    Number(s.warehouse_id) === Number(warehouseId) &&
                    Number(s.aid_item_id) === Number(item.aid_item_id)
                );

                if (
                    !stock ||
                    !Number.isInteger(Number(quantity)) ||
                    Number(quantity) < 1 ||
                    Number(quantity) > Number(stock.available)
                ) {
                    invalid = true;
                }
            });
        });

        submitButton.disabled = !hasAllocation || invalid;
    }

    function refresh() {
        renderItems();
        renderMatching();
        renderAllocations();
    }

    requestSelect.addEventListener('change', function () {
        currentRequest = requests.find(item =>
            Number(item.id) === Number(this.value)
        ) || null;

        selectedAllocations = {};

        if (!currentRequest) {
            document.getElementById('request-info').style.display = 'none';

            itemsContainer.innerHTML =
                '<p class="text-muted">Pilih permintaan bantuan terlebih dahulu.</p>';

            matchingContainer.innerHTML =
                '<div class="alert alert-secondary">Pilih permintaan bantuan untuk melihat rekomendasi.</div>';

            allocationContainer.innerHTML =
                '<p class="text-muted">Belum ada alokasi barang.</p>';

            allocationSummary.style.display = 'none';
            submitButton.disabled = true;
            return;
        }

        document.getElementById('request-info').style.display = 'block';

        document.getElementById('request-location').textContent =
            'Lokasi laporan: ' +
            (currentRequest.latitude && currentRequest.longitude
                ? currentRequest.latitude + ', ' + currentRequest.longitude
                : 'Koordinat tidak tersedia');

        document.getElementById('request-status').textContent =
            'Status: ' + currentRequest.status;

        refresh();
    });

    matchingContainer.addEventListener('click', function (event) {
        const button = event.target.closest('.select-warehouse');

        if (!button || button.disabled) return;

        const itemId = button.dataset.item;
        const warehouseId = button.dataset.warehouse;

        const stock = stocks.find(s =>
            Number(s.aid_item_id) === Number(itemId) &&
            Number(s.warehouse_id) === Number(warehouseId)
        );

        if (!stock) return;

        if (!selectedAllocations[itemId]) {
            selectedAllocations[itemId] = {};
        }

        if (selectedAllocations[itemId][warehouseId]) return;

        const remaining = getRemaining(itemId);

        if (remaining <= 0) {
            alert('Kebutuhan barang ini sudah terpenuhi.');
            return;
        }

        selectedAllocations[itemId][warehouseId] = Math.min(
            Number(stock.available),
            remaining
        );

        refresh();
    });

    allocationContainer.addEventListener('input', function (event) {
        if (!event.target.classList.contains('allocation-quantity')) return;

        const itemId = event.target.dataset.item;
        const warehouseId = event.target.dataset.warehouse;

        if (!selectedAllocations[itemId]) return;

        selectedAllocations[itemId][warehouseId] = Number(event.target.value);

        updateSummary();
    });

    allocationContainer.addEventListener('change', function (event) {
        if (!event.target.classList.contains('allocation-quantity')) return;

        refresh();
    });

    allocationContainer.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-allocation');

        if (!button) return;

        const itemId = button.dataset.item;
        const warehouseId = button.dataset.warehouse;

        delete selectedAllocations[itemId][warehouseId];

        if (Object.keys(selectedAllocations[itemId]).length === 0) {
            delete selectedAllocations[itemId];
        }

        refresh();
    });

    document.getElementById('distribution-form')
        .addEventListener('submit', function (event) {
            if (submitButton.disabled) {
                event.preventDefault();
                alert('Periksa kembali alokasi barang dan stok gudang.');
            }
        });

    if (requestSelect.value) {
        requestSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
