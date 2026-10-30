
@extends('layouts.app')

@section('title', 'Peta GIS - RESQNET')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<link rel="stylesheet"
      href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">

<style>
    .gis-header {
        position: relative;
        overflow: hidden;
        background: var(--ink);
        color: #fff;
        padding: 26px 28px 26px 46px;
        border-radius: 6px;
        box-shadow: 6px 6px 0 var(--or);
        margin-bottom: 25px;
    }

    .gis-header::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 12px;
        background: var(--hz);
    }

    .gis-header h2 {
        color: #fff !important;
        text-transform: uppercase;
        font-stretch: 80%;
    }

    .gis-header i {
        color: var(--or);
    }

    .gis-stat {
        height: 100%;
    }

    .gis-stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--ink);
        border-radius: 4px;
        font-size: 22px;
    }

    .gis-stat-icon.bg-danger-subtle {
        background: var(--rd) !important;
        color: #fff !important;
    }

    .gis-stat-icon.bg-success-subtle {
        background: var(--ok-soft) !important;
        color: var(--ink) !important;
    }

    .map-card {
        overflow: hidden;
    }

    #resqnet-map {
        width: 100%;
        height: 560px;
        z-index: 1;
    }

    .map-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
        font-weight: 600;
    }

    .legend-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
        border: 2px solid var(--ink);
    }

    .legend-line {
        display: inline-block;
        width: 26px;
        height: 4px;
        margin-right: 6px;
        vertical-align: middle;
        background: #1677ff;
    }

    .recommendation-box {
        background: #f1f7ff;
        border: 2px solid var(--ink);
        border-left: 8px solid #1677ff;
        border-radius: 4px;
        padding: 10px;
        margin-top: 9px;
    }

    .recommendation-item {
        border-bottom: 1px solid rgba(18,20,23,.2);
        padding: 9px 0;
    }

    .recommendation-item:last-child {
        border-bottom: none;
    }

    .show-route {
        width: 100%;
    }

    .leaflet-popup-content {
        line-height: 1.6;
        margin: 12px;
    }

    .leaflet-popup-content button {
        cursor: pointer !important;
        pointer-events: auto !important;
    }

    .leaflet-routing-container {
        max-height: 220px;
        overflow-y: auto;
        font-size: 12px;
        border: 2px solid var(--ink) !important;
        border-radius: 4px;
        box-shadow: 4px 4px 0 var(--ink);
    }

    .severity-label {
        display: inline-block;
        padding: 3px 9px;
        border: 2px solid var(--ink);
        border-radius: 3px;
        font-size: 11px;
        font-weight: 700;
    }

    .severity-rendah {
        background: var(--ok-soft);
        color: var(--ink);
    }

    .severity-sedang {
        background: var(--yl);
        color: var(--ink);
    }

    .severity-tinggi {
        background: #ffd9c7;
        color: var(--ink);
    }

    .severity-kritis {
        background: var(--rd);
        color: #fff;
    }

    .stock-available {
        color: #198754;
        font-weight: 700;
    }

    .stock-empty {
        color: #dc3545;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        #resqnet-map {
            height: 420px;
        }

        .gis-header {
            padding: 20px 20px 20px 34px;
        }

        .map-legend {
            gap: 12px;
        }
    }
</style>
@endpush

@section('content')

<div class="container-fluid py-3">

    <!-- HEADER -->
    <div class="gis-header">
        <div class="d-flex align-items-center gap-3">
            <div>
                <i class="bi bi-geo-alt-fill" style="font-size:35px;"></i>
            </div>

            <div>
                <h2 class="fw-bold mb-1">Peta Persebaran Bencana</h2>
                <p class="mb-0 opacity-75">
                    Monitoring lokasi bencana, kebutuhan bantuan, dan rekomendasi gudang RESQNET.
                </p>
            </div>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="card gis-stat">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="gis-stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div>
                        <small class="text-muted">Laporan Terverifikasi</small>
                        <h3 class="fw-bold mb-0 text-danger">
                            {{ $totalReports }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card gis-stat">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="gis-stat-icon bg-success-subtle text-success">
                        <i class="bi bi-house-check-fill"></i>
                    </div>

                    <div>
                        <small class="text-muted">Gudang Bantuan Aktif</small>
                        <h3 class="fw-bold mb-0 text-success">
                            {{ $totalWarehouses }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- LEGENDA -->
    <div class="card map-card mb-3">
        <div class="card-body">
            <div class="map-legend">

                <strong>Legenda Peta:</strong>

                <span>
                    <span class="legend-dot" style="background:#e5242b;"></span>
                    Lokasi Bencana
                </span>

                <span>
                    <span class="legend-dot" style="background:#1f9d55;"></span>
                    Gudang Bantuan
                </span>

                <span>
                    <span class="legend-line"></span>
                    Rute Distribusi
                </span>

            </div>
        </div>
    </div>

    <!-- PETA -->
    <div class="card map-card mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-map me-2 text-primary"></i>
                Peta GIS RESQNET
            </h5>

            <small class="text-muted">
                Klik marker untuk melihat detail bencana, kebutuhan bantuan, dan rute menuju gudang.
            </small>
        </div>

        <div class="card-body p-0">
            <div id="resqnet-map"></div>
        </div>

    </div>

    <!-- TABEL LAPORAN -->
    <div class="card map-card mb-4">

        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-list-ul me-2 text-primary"></i>
                Daftar Lokasi Bencana Terverifikasi
            </h5>
        </div>

        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Judul Laporan</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Keparahan</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($reports as $report)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="fw-semibold">
                                {{ $report->title }}
                            </td>

                            <td>
                                {{ $report->category->name ?? '-' }}
                            </td>

                            <td>
                                {{ $report->location->name ?? '-' }}
                            </td>

                            <td>
                                <span class="severity-label severity-{{ $report->severity }}">
                                    {{ ucfirst($report->severity) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-success">
                                    Terverifikasi
                                </span>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-geo-alt fs-3 d-block mb-2"></i>
                                Belum ada laporan bencana terverifikasi.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection

@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // 1. DATA DARI LARAVEL
    // ==========================================

    const reports = @json($reportData);
    const warehouses = @json($warehouseData);

    // ==========================================
    // 2. INISIALISASI PETA
    // ==========================================

    const map = L.map('resqnet-map').setView(
        [-3.8004, 102.2655],
        11
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const markers = [];

    let currentRoute = null;

    // ==========================================
    // 3. FUNGSI KEAMANAN TEKS
    // ==========================================

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

    // ==========================================
    // 4. VALIDASI KOORDINAT
    // ==========================================

    function validCoordinates(lat, lng) {
        return Number.isFinite(lat)
            && Number.isFinite(lng)
            && lat >= -90
            && lat <= 90
            && lng >= -180
            && lng <= 180;
    }

    // ==========================================
    // 5. MARKER BENCANA
    // ==========================================

    reports.forEach(function (report) {

        const latitude = Number(report.latitude);
        const longitude = Number(report.longitude);

        if (!validCoordinates(latitude, longitude)) {
            return;
        }

        const marker = L.circleMarker(
            [latitude, longitude],
            {
                radius: 10,
                color: '#ffffff',
                weight: 2,
                fillColor: '#e5242b',
                fillOpacity: 0.95
            }
        ).addTo(map);

        // Informasi kebutuhan bantuan
        let needsHtml = '';

        if (report.aid_requests && report.aid_requests.length > 0) {

            needsHtml = report.aid_requests.map(function (item) {

                let recommendationHtml = '';

                if (
                    item.recommendations &&
                    item.recommendations.length > 0
                ) {

                    recommendationHtml = `
                        <div class="recommendation-box">

                            <strong style="color:#1677ff;">
                                <i class="bi bi-sign-turn-right-fill"></i>
                                Gudang dan Rute Distribusi
                            </strong>

                            ${item.recommendations.map(function (warehouse, index) {

                                const startLat = Number(warehouse.warehouse_latitude);
                                const startLng = Number(warehouse.warehouse_longitude);

                                const availableStock = Number(warehouse.available_stock ?? 0);
                                const recommendedQuantity = Number(warehouse.recommended_quantity ?? 0);

                                const hasStock = availableStock > 0;

                                const stockLabel = hasStock
                                    ? 'Stok tersedia'
                                    : 'Stok kosong atau barang belum tersedia';

                                const stockClass = hasStock
                                    ? 'stock-available'
                                    : 'stock-empty';

                                const distanceLabel = warehouse.distance_km !== null
                                    && warehouse.distance_km !== undefined
                                    ? Number(warehouse.distance_km).toFixed(2) + ' km'
                                    : 'Belum dapat dihitung';

                                return `
                                    <div class="recommendation-item">

                                        <strong>
                                            ${index + 1}. ${escapeHtml(warehouse.warehouse_name)}
                                        </strong>

                                        <br>

                                        <small class="${stockClass}">
                                            <i class="bi ${hasStock ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}"></i>
                                            ${stockLabel}
                                        </small>

                                        <br>

                                        <small>
                                            Stok tersedia:
                                            <strong>
                                                ${availableStock}
                                                ${escapeHtml(warehouse.unit)}
                                            </strong>
                                        </small>

                                        <br>

                                        <small>
                                            Jumlah rekomendasi:
                                            <strong>
                                                ${recommendedQuantity}
                                                ${escapeHtml(warehouse.unit)}
                                            </strong>
                                        </small>

                                        <br>

                                        <small>
                                            Jarak garis lurus:
                                            <strong>${escapeHtml(distanceLabel)}</strong>
                                        </small>

                                        <br>

                                        <small class="text-muted">
                                            ${escapeHtml(warehouse.warehouse_address || 'Alamat belum tersedia')}
                                        </small>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary mt-2 show-route"
                                            data-start-lat="${startLat}"
                                            data-start-lng="${startLng}"
                                            data-end-lat="${latitude}"
                                            data-end-lng="${longitude}"
                                            data-warehouse="${escapeHtml(warehouse.warehouse_name)}"
                                            onclick="window.showResqnetRoute(this)"
                                        >
                                            <i class="bi bi-sign-turn-right me-1"></i>
                                            Lihat Rute
                                        </button>

                                    </div>
                                `;

                            }).join('')}

                        </div>
                    `;

                } else {

                    recommendationHtml = `
                        <div class="text-muted mt-2">
                            <small>
                                Belum tersedia gudang dengan koordinat valid untuk menampilkan rute.
                            </small>
                        </div>
                    `;
                }

                return `
                    <div class="mb-3">

                        <strong>${escapeHtml(item.item_name)}</strong>

                        <br>

                        Kebutuhan:
                        ${escapeHtml(item.quantity)}
                        ${escapeHtml(item.unit)}

                        <br>

                        Sudah dialokasikan:
                        ${escapeHtml(item.allocated_quantity ?? 0)}
                        ${escapeHtml(item.unit)}

                        <br>

                        Sisa kebutuhan:
                        ${escapeHtml(item.remaining_quantity ?? 0)}
                        ${escapeHtml(item.unit)}

                        <br>

                        Prioritas:
                        ${escapeHtml(item.priority)}

                        <br>

                        Status:
                        ${escapeHtml(item.status)}

                        ${recommendationHtml}

                    </div>
                `;

            }).join('');

        } else {

            needsHtml = `
                <span class="text-muted">
                    Belum ada kebutuhan bantuan.
                </span>
            `;
        }

        // Popup marker
        marker.bindPopup(`
            <div style="min-width:240px;max-width:310px;">

                <h6 class="fw-bold text-danger mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    ${escapeHtml(report.title)}
                </h6>

                <hr>

                <strong>Informasi Bencana</strong>

                <br>

                Kategori:
                ${escapeHtml(report.category)}

                <br>

                Keparahan:
                ${escapeHtml(report.severity)}

                <br>

                Lokasi:
                ${escapeHtml(report.location)}

                <br>

                Alamat:
                ${escapeHtml(report.address)}

                <br>

                Kecamatan:
                ${escapeHtml(report.district)}

                <br>

                Kota:
                ${escapeHtml(report.city)}

                <br>

                Provinsi:
                ${escapeHtml(report.province)}

                <hr>

                <strong class="text-primary">
                    <i class="bi bi-box-seam me-1"></i>
                    Kebutuhan dan Rekomendasi Bantuan
                </strong>

                <div class="mt-2">
                    ${needsHtml}
                </div>

            </div>
        `);

        markers.push(marker);

    });

    // ==========================================
    // 6. MARKER GUDANG BANTUAN
    // ==========================================

    warehouses.forEach(function (warehouse) {

        const latitude = Number(warehouse.latitude);
        const longitude = Number(warehouse.longitude);

        if (!validCoordinates(latitude, longitude)) {
            return;
        }

        const marker = L.marker([
            latitude,
            longitude
        ]).addTo(map);

        let stocksHtml = '';

        if (warehouse.stocks && warehouse.stocks.length > 0) {

            stocksHtml = warehouse.stocks.map(function (stock) {

                const available = Number(stock.available_quantity);

                const stockColor = available > 0
                    ? '#1f9d55'
                    : '#e5242b';

                return `
                    <div style="border-bottom:1px solid #eee;padding:8px 0;">

                        <strong>${escapeHtml(stock.item_name)}</strong>

                        <br>

                        <small>
                            Total stok:
                            ${escapeHtml(stock.quantity)}
                            ${escapeHtml(stock.unit)}
                        </small>

                        <br>

                        <small>
                            Dicadangkan:
                            ${escapeHtml(stock.reserved_quantity)}
                            ${escapeHtml(stock.unit)}
                        </small>

                        <br>

                        <small style="color:${stockColor};">
                            <strong>
                                Tersedia:
                                ${escapeHtml(stock.available_quantity)}
                                ${escapeHtml(stock.unit)}
                            </strong>
                        </small>

                    </div>
                `;

            }).join('');

        } else {

            stocksHtml = `
                <small class="text-muted">
                    Belum ada data stok bantuan.
                </small>
            `;
        }

        marker.bindPopup(`
            <div style="min-width:230px;max-width:300px;">

                <h6 class="fw-bold text-success">
                    <i class="bi bi-house-check-fill me-1"></i>
                    Gudang Bantuan
                </h6>

                <hr>

                <strong>Nama Gudang:</strong>
                <br>
                ${escapeHtml(warehouse.name)}

                <br><br>

                <strong>Alamat:</strong>
                <br>
                ${escapeHtml(warehouse.address || 'Belum tersedia')}

                <hr>

                <strong class="text-primary">
                    Informasi Stok Bantuan
                </strong>

                <div class="mt-2">
                    ${stocksHtml}
                </div>

            </div>
        `);

        markers.push(marker);

    });

    // ==========================================
    // 7. FUNGSI LIHAT RUTE
    // ==========================================

    window.showResqnetRoute = function (button) {

        const startLat = parseFloat(button.dataset.startLat);
        const startLng = parseFloat(button.dataset.startLng);
        const endLat = parseFloat(button.dataset.endLat);
        const endLng = parseFloat(button.dataset.endLng);

        const warehouseName = button.dataset.warehouse;

        if (
            !validCoordinates(startLat, startLng) ||
            !validCoordinates(endLat, endLng)
        ) {
            alert('Koordinat gudang atau lokasi bencana tidak valid.');
            return;
        }

        if (typeof L.Routing === 'undefined') {
            alert(
                'Plugin rute belum berhasil dimuat. Periksa koneksi internet.'
            );
            return;
        }

        if (currentRoute) {
            map.removeControl(currentRoute);
            currentRoute = null;
        }

        currentRoute = L.Routing.control({

            waypoints: [
                L.latLng(startLat, startLng),
                L.latLng(endLat, endLng)
            ],

            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: true,

            lineOptions: {
                styles: [
                    {
                        color: '#1677ff',
                        opacity: 0.9,
                        weight: 6
                    }
                ]
            },

            createMarker: function (index, waypoint) {

                const label = index === 0
                    ? 'Gudang: ' + warehouseName
                    : 'Lokasi Bencana';

                return L.marker(waypoint.latLng)
                    .bindPopup(escapeHtml(label));
            }

        }).addTo(map);

        currentRoute.on('routingerror', function () {
            alert(
                'Rute tidak berhasil dihitung. Periksa koneksi internet atau coba kembali.'
            );
        });

        map.closePopup();

    };

    // ==========================================
    // 8. MENYESUAIKAN PETA DENGAN MARKER
    // ==========================================

    if (markers.length > 0) {

        const group = L.featureGroup(markers);

        map.fitBounds(group.getBounds().pad(0.15));

    }

    setTimeout(function () {
        map.invalidateSize();
    }, 300);

});
</script>

@endpush
