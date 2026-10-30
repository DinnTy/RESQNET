@extends('layouts.app')

@section('title', 'Buat Laporan Bencana - RESQNET')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
    /* Tema Siaga — hanya tampilan; nama class & fungsi peta tidak diubah */
    .report-wrapper { max-width: 920px; margin: 0 auto; }

    .report-header {
        position: relative; overflow: hidden;
        background: var(--ink); color: #fff;
        padding: 28px 28px 28px 46px;
        border-radius: 6px; box-shadow: 6px 6px 0 var(--or);
        margin-bottom: 25px;
    }
    .report-header::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 12px; background: var(--hz); }
    .report-header h1, .report-header h2, .report-header h3, .report-header h4 { color: #fff !important; text-transform: uppercase; font-stretch: 80%; }
    .report-header p { color: #cfcdc7; }

    .report-card {
        background: #fff;
        border: 2px solid var(--ink); border-radius: 6px;
        box-shadow: 5px 5px 0 var(--ink);
        padding: 28px; margin-bottom: 22px;
    }

    .section-title {
        display: flex; align-items: center; gap: 10px;
        font: 800 17px var(--dsp); text-transform: uppercase; color: var(--ink);
        margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px dashed var(--ink);
    }

    .map-container { display: none; margin-top: 16px; }

    #locationMap {
        width: 100%; height: 380px; z-index: 1;
        border: 3px solid var(--ink); border-radius: 6px; box-shadow: 5px 5px 0 var(--ink);
    }

    .location-info {
        background: #fff6f1; border: 2px solid var(--ink); border-left: 8px solid var(--or);
        border-radius: 4px; padding: 13px; color: var(--ink);
        font-size: 13px; font-weight: 500; margin-top: 13px;
    }

    .coordinate-status {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 6px 12px; margin-top: 12px;
        background: #fff; border: 2px solid var(--ink); border-radius: 3px;
        color: var(--ink); font: 600 12px var(--mono);
    }
    .coordinate-status.selected { background: var(--ok-soft); }

    .btn-map {
        background: #fff; color: var(--ink);
        border: 2px solid var(--ink) !important; border-radius: 4px;
        padding: 10px 15px; font-weight: 700;
    }
    .btn-map:hover { background: var(--yl); color: var(--ink); }

    .required-mark { color: var(--rd); }

    @media (max-width: 576px) {
        .report-header, .report-card { padding: 20px; }
        .report-header { padding-left: 34px; }
        #locationMap { height: 300px; }
        .form-actions .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="report-wrapper">

    <div class="report-header">
        <div class="d-flex align-items-center gap-3 mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
            <div>
                <h3 class="fw-bold mb-1">Buat Laporan Bencana</h3>
                <p class="mb-0 opacity-75">
                    Bantu kami mengetahui kejadian bencana di sekitar Anda.
                </p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-3">
            <strong>Periksa kembali data laporan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reports.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <!-- INFORMASI LAPORAN -->
        <div class="report-card">
            <h5 class="section-title">
                <i class="bi bi-file-earmark-text me-2"></i>
                Informasi Laporan
            </h5>

            <div class="mb-3">
                <label class="form-label">
                    Judul Laporan <span class="required-mark">*</span>
                </label>
                <input type="text"
                       name="title"
                       class="form-control"
                       value="{{ old('title') }}"
                       placeholder="Contoh: Banjir di Kecamatan Selebar"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Kategori Bencana <span class="required-mark">*</span>
                </label>
                <select name="category_id" class="form-select" required>
                    <option value="">Pilih kategori bencana</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Deskripsi Kejadian <span class="required-mark">*</span>
                </label>
                <textarea name="description"
                          class="form-control"
                          rows="4"
                          placeholder="Jelaskan kondisi dan dampak kejadian bencana..."
                          required>{{ old('description') }}</textarea>
            </div>

            <div class="mb-0">
                <label class="form-label">
                    Tingkat Keparahan <span class="required-mark">*</span>
                </label>
                <select name="severity" class="form-select" required>
                    <option value="rendah" {{ old('severity') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                    <option value="sedang" {{ old('severity', 'sedang') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="tinggi" {{ old('severity') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="kritis" {{ old('severity') == 'kritis' ? 'selected' : '' }}>Kritis</option>
                </select>
            </div>
        </div>

        <!-- INFORMASI LOKASI -->
        <div class="report-card">
            <h5 class="section-title">
                <i class="bi bi-geo-alt-fill me-2"></i>
                Informasi Lokasi Kejadian
            </h5>

            <div class="mb-3">
                <label class="form-label">
                    Nama Lokasi <span class="required-mark">*</span>
                </label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Kelurahan Rawa Makmur"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="address"
                          class="form-control"
                          rows="2"
                          placeholder="Nama jalan, RT/RW, atau patokan lokasi">{{ old('address') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kecamatan</label>
                    <input type="text"
                           name="district"
                           class="form-control"
                           value="{{ old('district') }}"
                           placeholder="Nama kecamatan">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Kota/Kabupaten</label>
                    <input type="text"
                           name="city"
                           class="form-control"
                           value="{{ old('city') }}"
                           placeholder="Nama kota atau kabupaten">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Provinsi</label>
                <input type="text"
                       name="province"
                       class="form-control"
                       value="{{ old('province') }}"
                       placeholder="Nama provinsi">
            </div>

            <div class="border-top pt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold mb-1">Titik Lokasi pada Peta</h6>
                        <small class="text-muted">
                            Pilih titik tempat kejadian bencana berlangsung.
                        </small>
                    </div>
                    <span id="coordinateStatus" class="coordinate-status">
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
                        Klik pada peta untuk menentukan lokasi kejadian.
                        Anda juga bisa menggeser pin untuk menyesuaikan posisi.
                    </div>

                    <div id="locationMap" class="mt-3"></div>
                </div>

                <!-- KOORDINAT DISIMPAN OTOMATIS -->
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
        </div>

        <!-- BUKTI DAN WAKTU -->
        <div class="report-card">
            <h5 class="section-title">
                <i class="bi bi-camera-fill me-2"></i>
                Bukti dan Waktu Kejadian
            </h5>

            <div class="mb-3">
                <label class="form-label">Foto Bukti (Opsional)</label>
                <input type="file"
                       name="evidence"
                       class="form-control"
                       accept="image/*">
                <small class="text-muted">
                    Format gambar, maksimal 2 MB.
                </small>
            </div>

            <div class="mb-0">
                <label class="form-label">Waktu Kejadian</label>
                <input type="datetime-local"
                       name="occurred_at"
                       class="form-control"
                       value="{{ old('occurred_at') }}">
            </div>
        </div>

        <!-- TOMBOL -->
        <div class="form-actions d-flex flex-wrap gap-2 justify-content-end mb-4">
            <a href="{{ route('reports.index') }}"
               class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali
            </a>

            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-send-fill me-2"></i>
                Kirim Laporan
            </button>
        </div>
    </form>
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
    const form = document.querySelector('form');

    // Titik awal peta: Bengkulu
    const defaultLatitude = -3.8004;
    const defaultLongitude = 102.2655;

    let map = null;
    let marker = null;
    let selectedLocation = false;

    function updateCoordinates(lat, lng) {
        latitudeInput.value = Number(lat).toFixed(7);
        longitudeInput.value = Number(lng).toFixed(7);

        selectedLocation = true;

        statusElement.classList.add('selected');
        statusElement.innerHTML =
            '<i class="bi bi-check-circle-fill"></i> Lokasi berhasil dipilih';
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

        const initialLat = hasOldLocation ? oldLat : defaultLatitude;
        const initialLng = hasOldLocation ? oldLng : defaultLongitude;

        map = L.map('locationMap').setView(
            [initialLat, initialLng],
            hasOldLocation ? 16 : 12
        );

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (hasOldLocation) {
            marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            updateCoordinates(initialLat, initialLng);

            marker.on('dragend', function (event) {
                const position = event.target.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });
        }

        map.on('click', function (event) {
            const lat = event.latlng.lat;
            const lng = event.latlng.lng;

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
        });

        setTimeout(() => map.invalidateSize(), 150);
    }

    toggleMapButton.addEventListener('click', function () {
        const isHidden = getComputedStyle(mapContainer).display === 'none';

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
            longitudeInput.value === ''
        ) {
            event.preventDefault();

            alert('Silakan pilih titik lokasi kejadian pada peta terlebih dahulu.');

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

    // Pertahankan koordinat lama jika validasi sebelumnya gagal
    if (latitudeInput.value !== '' && longitudeInput.value !== '') {
        updateCoordinates(
            parseFloat(latitudeInput.value),
            parseFloat(longitudeInput.value)
        );
    }
});
</script>
@endpush
