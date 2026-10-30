
@extends('layouts.app')

@section('title', 'Tambah Gudang | RESQNET')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
    .warehouse-wrapper {
        max-width: 1100px;
        margin: 0 auto;
    }

    .warehouse-card {
        background: #fff;
        border: 2px solid var(--ink);
        border-radius: 6px;
        box-shadow: 5px 5px 0 var(--ink);
    }

    .warehouse-section-title {
        font-weight: 800;
        color: var(--ink);
        margin-bottom: 20px;
    }

    .map-container {
        display: none;
        margin-top: 16px;
    }

    #warehouseMap {
        width: 100%;
        height: 380px;
        border: 3px solid var(--ink);
        border-radius: 6px;
        box-shadow: 5px 5px 0 var(--ink);
        z-index: 1;
    }

    .location-info {
        background: #fff6f1;
        border: 2px solid var(--ink);
        border-left: 8px solid var(--or);
        border-radius: 4px;
        padding: 13px;
        color: var(--ink);
        font-size: 13px;
        margin-top: 13px;
    }

    .coordinate-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px;
        background: #fff;
        border: 2px solid var(--ink);
        border-radius: 3px;
        color: var(--ink);
        font: 600 12px var(--mono);
    }

    .coordinate-status.selected {
        background: var(--ok-soft);
    }

    .btn-map {
        background: #fff;
        color: var(--ink);
        border: 2px solid var(--ink) !important;
        border-radius: 4px;
        padding: 10px 15px;
        font-weight: 700;
    }

    .btn-map:hover {
        background: var(--yl);
        color: var(--ink);
    }

    @media (max-width: 576px) {
        #warehouseMap {
            height: 300px;
        }
    }
</style>
@endpush

@section('content')
<div class="warehouse-wrapper py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Tambah Gudang Bantuan</h3>
            <p class="text-muted mb-0">
                Tambahkan lokasi gudang untuk mendukung distribusi bantuan RESQNET.
            </p>
        </div>

        <a href="{{ route('warehouses.index') }}"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card warehouse-card">
        <div class="card-body p-4">

            <form action="{{ route('warehouses.store') }}"
                  method="POST"
                  id="warehouseForm">
                @csrf

                <h5 class="warehouse-section-title">
                    Informasi Gudang
                </h5>

                <div class="mb-3">
                    <label class="form-label">Nama Gudang</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           placeholder="Contoh: Gudang Bantuan Selebar"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Gudang</label>
                    <textarea name="address"
                              class="form-control"
                              rows="3"
                              placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Pengelola</label>
                        <input type="text"
                               name="manager_name"
                               class="form-control"
                               value="{{ old('manager_name') }}"
                               placeholder="Nama pengelola">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone') }}"
                               placeholder="Nomor telepon">
                    </div>
                </div>

                <h5 class="warehouse-section-title mt-4">
                    Lokasi Geografis
                </h5>

                <div class="border-top pt-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div>
                            <h6 class="fw-bold mb-1">
                                Titik Lokasi pada Peta
                            </h6>
                            <small class="text-muted">
                                Pilih titik lokasi gudang yang akan didaftarkan.
                            </small>
                        </div>

                        <span id="coordinateStatus"
                              class="coordinate-status">
                            <i class="bi bi-geo"></i>
                            Lokasi belum dipilih
                        </span>
                    </div>

                    <button type="button"
                            class="btn btn-map mt-2"
                            id="toggleMap">
                        <i class="bi bi-map me-2"></i>
                        Pilih Lokasi pada Peta
                    </button>

                    <button type="button"
                            class="btn btn-outline-success mt-2"
                            id="locateMe">
                        <i class="bi bi-crosshair me-2"></i>
                        Gunakan Lokasi Saya
                    </button>

                    <div class="map-container" id="mapContainer">

                        <div class="location-info">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Klik pada peta untuk menentukan lokasi gudang.
                            Anda juga bisa menggeser pin untuk menyesuaikan posisi.
                        </div>

                        <div id="warehouseMap" class="mt-3"></div>
                    </div>

                    <!-- Koordinat tetap dikirim ke backend -->
                    <input type="hidden"
                           name="latitude"
                           id="latitude"
                           value="{{ old('latitude') }}"
                           required>

                    <input type="hidden"
                           name="longitude"
                           id="longitude"
                           value="{{ old('longitude') }}"
                           required>

                    <div class="small text-muted mt-3">
                        <i class="bi bi-shield-check me-1"></i>
                        Koordinat lokasi akan disimpan otomatis oleh sistem.
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kecamatan</label>
                        <input type="text"
                               name="district"
                               class="form-control"
                               value="{{ old('district') }}"
                               placeholder="Selebar">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kota/Kabupaten</label>
                        <input type="text"
                               name="city"
                               class="form-control"
                               value="{{ old('city', 'Bengkulu') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text"
                               name="province"
                               class="form-control"
                               value="{{ old('province', 'Bengkulu') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status Gudang</label>
                    <select name="is_active"
                            class="form-select"
                            required>
                        <option value="1"
                            {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="0"
                            {{ old('is_active') == '0' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warehouses.index') }}"
                       class="btn btn-light border">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Simpan Gudang
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const mapContainer = document.getElementById('mapContainer');
    const toggleMapButton = document.getElementById('toggleMap');
    const locateButton = document.getElementById('locateMe');

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    const statusElement = document.getElementById('coordinateStatus');
    const form = document.getElementById('warehouseForm');

    // Titik awal peta: Bengkulu
    const defaultLatitude = -3.8004;
    const defaultLongitude = 102.2655;

    let map = null;
    let marker = null;

    function updateCoordinates(lat, lng) {

        latitudeInput.value = Number(lat).toFixed(7);
        longitudeInput.value = Number(lng).toFixed(7);

        statusElement.classList.add('selected');

        statusElement.innerHTML =
            '<i class="bi bi-check-circle-fill"></i> Lokasi berhasil dipilih';
    }

    function addMarker(lat, lng) {

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {

            marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            marker.on('dragend', function (event) {

                const position = event.target.getLatLng();

                updateCoordinates(position.lat, position.lng);
            });
        }

        updateCoordinates(lat, lng);
    }

    function initializeMap() {

        if (map) {
            setTimeout(() => map.invalidateSize(), 150);
            return;
        }

        const oldLat = parseFloat(latitudeInput.value);
        const oldLng = parseFloat(longitudeInput.value);

        const hasOldLocation =
            latitudeInput.value !== '' &&
            longitudeInput.value !== '' &&
            Number.isFinite(oldLat) &&
            Number.isFinite(oldLng);

        const initialLat = hasOldLocation
            ? oldLat
            : defaultLatitude;

        const initialLng = hasOldLocation
            ? oldLng
            : defaultLongitude;

        map = L.map('warehouseMap').setView(
            [initialLat, initialLng],
            hasOldLocation ? 16 : 12
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (hasOldLocation) {
            addMarker(initialLat, initialLng);
        }

        map.on('click', function (event) {

            const lat = event.latlng.lat;
            const lng = event.latlng.lng;

            addMarker(lat, lng);
        });

        setTimeout(() => map.invalidateSize(), 150);
    }

    toggleMapButton.addEventListener('click', function () {

        const isHidden =
            getComputedStyle(mapContainer).display === 'none';

        if (isHidden) {

            mapContainer.style.display = 'block';

            toggleMapButton.innerHTML =
                '<i class="bi bi-map me-2"></i> Sembunyikan Peta';

            initializeMap();

        } else {

            mapContainer.style.display = 'none';

            toggleMapButton.innerHTML =
                '<i class="bi bi-map me-2"></i> Pilih Lokasi pada Peta';
        }
    });

    locateButton.addEventListener('click', function () {

        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung fitur lokasi.');
            return;
        }

        locateButton.disabled = true;

        locateButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span> Mencari lokasi...';

        navigator.geolocation.getCurrentPosition(

            function (position) {

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                mapContainer.style.display = 'block';

                toggleMapButton.innerHTML =
                    '<i class="bi bi-map me-2"></i> Sembunyikan Peta';

                initializeMap();

                map.setView([lat, lng], 17);

                addMarker(lat, lng);

                setTimeout(() => map.invalidateSize(), 150);

                locateButton.disabled = false;

                locateButton.innerHTML =
                    '<i class="bi bi-crosshair me-2"></i> Gunakan Lokasi Saya';
            },

            function (error) {

                let message = 'Lokasi tidak dapat ditemukan.';

                if (error.code === 1) {
                    message = 'Izin lokasi ditolak. Silakan izinkan akses lokasi atau pilih titik pada peta.';
                } else if (error.code === 2) {
                    message = 'Informasi lokasi tidak tersedia. Silakan pilih titik pada peta.';
                } else if (error.code === 3) {
                    message = 'Pencarian lokasi terlalu lama. Silakan coba lagi.';
                }

                alert(message);

                locateButton.disabled = false;

                locateButton.innerHTML =
                    '<i class="bi bi-crosshair me-2"></i> Gunakan Lokasi Saya';
            },

            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    });

    form.addEventListener('submit', function (event) {

        if (
            latitudeInput.value === '' ||
            longitudeInput.value === '' ||
            !Number.isFinite(parseFloat(latitudeInput.value)) ||
            !Number.isFinite(parseFloat(longitudeInput.value))
        ) {

            event.preventDefault();

            alert('Silakan pilih titik lokasi gudang pada peta terlebih dahulu.');

            mapContainer.style.display = 'block';

            toggleMapButton.innerHTML =
                '<i class="bi bi-map me-2"></i> Sembunyikan Peta';

            initializeMap();

            mapContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    });

    // Pertahankan koordinat jika validasi sebelumnya gagal
    if (
        latitudeInput.value !== '' &&
        longitudeInput.value !== '' &&
        Number.isFinite(parseFloat(latitudeInput.value)) &&
        Number.isFinite(parseFloat(longitudeInput.value))
    ) {

        updateCoordinates(
            parseFloat(latitudeInput.value),
            parseFloat(longitudeInput.value)
        );
    }
});
</script>
@endpush
