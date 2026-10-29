
@extends('layouts.app')

@section('title', 'Detail Permintaan Bantuan - RESQNET')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Detail Permintaan Bantuan</h3>
        <p class="text-muted mb-0">
            Informasi lengkap pengajuan kebutuhan bantuan.
        </p>
    </div>

    <a href="{{ route('aid-requests.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row g-4">

    {{-- INFORMASI PENGAJUAN --}}
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-clipboard-check text-primary"></i>
                    Informasi Pengajuan
                </h5>

                @php
                    $statusColors = [
                        'menunggu' => 'warning',
                        'disetujui' => 'success',
                        'diproses' => 'primary',
                        'terpenuhi' => 'success',
                        'ditolak' => 'danger'
                    ];

                    $priorityColors = [
                        'rendah' => 'secondary',
                        'sedang' => 'info',
                        'tinggi' => 'warning',
                        'kritis' => 'danger'
                    ];
                @endphp

                <table class="table table-borderless">
                    <tr>
                        <th width="35%">Kode Pengajuan</th>
                        <td>#{{ $aidRequest->id }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-{{ $statusColors[$aidRequest->status] ?? 'secondary' }}">
                                {{ ucfirst($aidRequest->status) }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>Tanggal Pengajuan</th>
                        <td>
                            {{ $aidRequest->created_at
                                ? $aidRequest->created_at->format('d-m-Y H:i')
                                : '-' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Keterangan</th>
                        <td>{{ $aidRequest->notes ?: '-' }}</td>
                    </tr>
                </table>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Daftar Kebutuhan Barang</h5>

                    <span class="badge bg-primary">
                        {{ $requestItems->count() > 0 ? $requestItems->count() : 1 }}
                        Jenis Barang
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Prioritas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($requestItems as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>
                                            {{ $item->aidItem->name ?? 'Barang tidak ditemukan' }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ number_format($item->quantity) }}
                                        {{ $item->unit }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $priorityColors[$item->priority] ?? 'secondary' }}">
                                            {{ ucfirst($item->priority) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <strong>{{ $aidRequest->item_name }}</strong>
                                    </td>
                                    <td>
                                        {{ number_format($aidRequest->quantity) }}
                                        {{ $aidRequest->unit }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $priorityColors[$aidRequest->priority] ?? 'secondary' }}">
                                            {{ ucfirst($aidRequest->priority) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="alert alert-light border mb-0">
                    <i class="bi bi-info-circle text-primary"></i>
                    Seluruh barang di atas merupakan satu pengajuan bantuan
                    yang terhubung dengan laporan bencana yang sama.
                </div>

            </div>
        </div>

        {{-- INFORMASI LAPORAN BENCANA --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-exclamation-triangle text-primary"></i>
                    Informasi Laporan Bencana
                </h5>

                <p class="mb-2">
                    <strong>Judul:</strong>
                    {{ $aidRequest->disasterReport->title ?? '-' }}
                </p>

                <p class="mb-2">
                    <strong>Jenis Bencana:</strong>
                    {{ $aidRequest->disasterReport->category->name ?? '-' }}
                </p>

                <p class="mb-2">
                    <strong>Lokasi:</strong>
                    {{ $aidRequest->disasterReport->location->name ?? '-' }}
                </p>

                <p class="mb-0">
                    <strong>Pelapor:</strong>
                    {{ $aidRequest->disasterReport->user->name ?? '-' }}
                </p>

            </div>
        </div>

    </div>

    {{-- SIDEBAR VERIFIKASI --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-shield-check text-primary"></i>
                    Proses Verifikasi
                </h5>

                @if($aidRequest->status === 'menunggu')
                    <div class="alert alert-warning">
                        Pengajuan sedang menunggu pemeriksaan petugas.
                    </div>
                @elseif($aidRequest->status === 'disetujui')
                    <div class="alert alert-success">
                        Pengajuan telah disetujui dan siap diproses.
                    </div>
                @elseif($aidRequest->status === 'diproses')
                    <div class="alert alert-primary">
                        Bantuan sedang dalam proses penanganan.
                    </div>
                @elseif($aidRequest->status === 'terpenuhi')
                    <div class="alert alert-success">
                        Kebutuhan bantuan telah terpenuhi.
                    </div>
                @elseif($aidRequest->status === 'ditolak')
                    <div class="alert alert-danger">
                        Pengajuan bantuan tidak disetujui.
                    </div>
                @endif

                @if(in_array(Auth::user()->role, ['admin', 'petugas']))

                    <hr>

                    <h6 class="fw-bold">Perbarui Status</h6>

                    <form action="{{ route('aid-requests.update-status', $aidRequest->id) }}"
                          method="POST">

                        @csrf
                        @method('PATCH')

                        <select name="status" class="form-select mb-3" required>
                            @foreach(['menunggu', 'disetujui', 'diproses', 'terpenuhi', 'ditolak'] as $status)
                                <option value="{{ $status }}"
                                    {{ $aidRequest->status === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i>
                            Simpan Status
                        </button>

                    </form>

                @endif

            </div>
        </div>

    </div>

</div>

{{-- INTELLIGENT GEOSPATIAL AID MATCHING --}}
@if(in_array(Auth::user()->role, ['admin', 'petugas']))

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body p-4">

        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-geo-alt-fill text-primary fs-4"></i>

            <h5 class="fw-bold mb-0">
                Intelligent Geospatial Aid Matching
            </h5>
        </div>

        <p class="text-muted">
            Sistem menganalisis kebutuhan setiap barang berdasarkan
            ketersediaan stok, jarak geografis, dan lokasi gudang.
        </p>

        <div class="alert alert-info">
            <strong>Lokasi Bencana:</strong>
            {{ $aidRequest->disasterReport->location->name ?? '-' }}

            <br>

            <strong>Jumlah Jenis Barang:</strong>
            {{ $itemMatchingPlans->count() }} jenis
        </div>

        @php
            $allFulfilled = $itemMatchingPlans->isNotEmpty()
                && $itemMatchingPlans->every(function ($plan) {
                    return $plan['is_fulfilled'];
                });

            $totalRequiredItems = $itemMatchingPlans->sum('required_quantity');
            $totalAvailableItems = $itemMatchingPlans->sum('total_available');
            $totalShortageItems = $itemMatchingPlans->sum('remaining_need');
        @endphp

        {{-- RINGKASAN KESELURUHAN --}}
        <h6 class="fw-bold mb-3">Ringkasan Kebutuhan Bantuan</h6>

        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Total Kebutuhan</small>

                    <h4 class="fw-bold mb-0">
                        {{ number_format($totalRequiredItems) }}
                    </h4>

                    <small>Gabungan jumlah seluruh barang</small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Total Stok Tersedia</small>

                    <h4 class="fw-bold text-success mb-0">
                        {{ number_format($totalAvailableItems) }}
                    </h4>

                    <small>Gabungan stok seluruh barang</small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <small class="text-muted">Total Kekurangan</small>

                    <h4 class="fw-bold text-danger mb-0">
                        {{ number_format($totalShortageItems) }}
                    </h4>

                    <small>Gabungan kekurangan seluruh barang</small>
                </div>
            </div>

        </div>

        <div class="alert alert-light border">
            <i class="bi bi-info-circle text-primary"></i>

            Total di atas merupakan penjumlahan angka kebutuhan dan stok.
            Karena setiap barang dapat memiliki satuan berbeda, penilaian
            pemenuhan tetap dilakukan secara terpisah untuk setiap barang.
        </div>

        {{-- MATCHING SETIAP BARANG --}}
        @foreach($itemMatchingPlans as $plan)

            @php
                $requestItem = $plan['request_item'];
                $aidItem = $plan['aid_item'];

                $matchingStocks = $plan['matching_stocks'];
                $allocationPlan = $plan['allocation_plan'];

                $required = $plan['required_quantity'];
                $available = $plan['total_available'];
                $shortage = $plan['remaining_need'];

                $percentage = $plan['fulfillment_percentage'];
                $fulfilled = $plan['is_fulfilled'];

                $itemName = $aidItem
                    ? $aidItem->name
                    : 'Barang tidak ditemukan';

                $itemUnit = $requestItem->unit ?? 'unit';
            @endphp

            <div class="border rounded-3 p-3 p-md-4 mb-4">

                {{-- IDENTITAS BARANG --}}
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">

                    <div>
                        <h5 class="fw-bold mb-1">
                            {{ $loop->iteration }}. {{ $itemName }}
                        </h5>

                        <small class="text-muted">
                            Prioritas:
                            {{ ucfirst($requestItem->priority ?? 'sedang') }}
                        </small>
                    </div>

                    @if(!$aidItem)
                        <span class="badge bg-secondary">
                            Barang tidak terdaftar
                        </span>
                    @elseif($fulfilled)
                        <span class="badge bg-success">
                            Stok mencukupi
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            Stok belum mencukupi
                        </span>
                    @endif

                </div>

                @if(!$aidItem)

                    <div class="alert alert-warning mb-0">
                        Barang ini belum ditemukan dalam katalog bantuan.
                        Petugas perlu memeriksa data barang yang terdaftar.
                    </div>

                @else

                    {{-- RINGKASAN STOK --}}
                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">Kebutuhan</small>

                                <h4 class="fw-bold mb-0">
                                    {{ number_format($required) }}
                                </h4>

                                <small>{{ $itemUnit }}</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">Stok Tersedia</small>

                                <h4 class="fw-bold text-success mb-0">
                                    {{ number_format($available) }}
                                </h4>

                                <small>{{ $itemUnit }}</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted">Kekurangan</small>

                                <h4 class="fw-bold text-danger mb-0">
                                    {{ number_format($shortage) }}
                                </h4>

                                <small>{{ $itemUnit }}</small>
                            </div>
                        </div>

                    </div>

                    {{-- PROGRESS PEMENUHAN --}}
                    <div class="border rounded p-3 mb-4">

                        <div class="d-flex justify-content-between mb-2">
                            <strong>Persentase Pemenuhan</strong>
                            <strong>{{ $percentage }}%</strong>
                        </div>

                        <div class="progress" style="height: 14px;">
                            <div
                                class="progress-bar {{ $fulfilled ? 'bg-success' : 'bg-primary' }}"
                                role="progressbar"
                                style="width: {{ $percentage }}%;"
                                aria-valuenow="{{ $percentage }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>

                    </div>

                    {{-- METODE REKOMENDASI --}}
                    <div class="alert alert-light border">

                        <h6 class="fw-bold">
                            <i class="bi bi-info-circle"></i>
                            Metode Rekomendasi
                        </h6>

                        @if($hasCoordinates)
                            Sistem menghitung jarak antara gudang dan lokasi
                            bencana menggunakan metode Haversine. Gudang
                            dengan stok tersedia diurutkan berdasarkan jarak.
                        @else
                            Koordinat lokasi bencana belum lengkap.
                            Rekomendasi diurutkan berdasarkan stok tersedia.
                        @endif

                    </div>

                    {{-- TABEL ALOKASI --}}
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-diagram-3"></i>
                        Rekomendasi Alokasi Gudang
                    </h6>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Gudang</th>
                                    <th>Jarak</th>
                                    <th>Stok Tersedia</th>
                                    <th>Rekomendasi Alokasi</th>
                                    <th>Alasan</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($allocationPlan as $allocation)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <strong>
                                                {{ $allocation['warehouse'] }}
                                            </strong>

                                            @if($loop->first)
                                                <span class="badge bg-success ms-1">
                                                    Prioritas 1
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($allocation['distance_km'] !== null)
                                                {{ number_format($allocation['distance_km'], 2) }} km
                                            @else
                                                <span class="text-muted">
                                                    Tidak tersedia
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ number_format($allocation['available']) }}
                                            {{ $itemUnit }}
                                        </td>

                                        <td>
                                            <strong class="text-primary">
                                                {{ number_format($allocation['recommended_quantity']) }}
                                                {{ $itemUnit }}
                                            </strong>
                                        </td>

                                        <td>
                                            <small>
                                                {{ $allocation['reason'] }}
                                            </small>
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Belum ditemukan stok tersedia
                                            pada gudang aktif.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- HASIL MATCHING BARANG --}}
                    @if($fulfilled)

                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle-fill"></i>

                            <strong>Kebutuhan barang dapat dipenuhi.</strong>

                            Stok tersedia mencukupi kebutuhan
                            {{ number_format($required) }}
                            {{ $itemUnit }}.
                        </div>

                    @else

                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i>

                            <strong>Stok belum mencukupi.</strong>

                            Masih terdapat kekurangan
                            {{ number_format($shortage) }}
                            {{ $itemUnit }}.
                        </div>

                    @endif

                @endif

            </div>

        @endforeach

        {{-- KESIMPULAN KESELURUHAN --}}
        @if($allFulfilled)

            <div class="alert alert-success mb-0">
                <h6 class="fw-bold">
                    <i class="bi bi-check-circle-fill"></i>
                    Seluruh Kebutuhan Memiliki Stok yang Mencukupi
                </h6>

                Sistem menemukan stok yang mencukupi untuk seluruh
                jenis barang yang diajukan.
            </div>

        @else

            <div class="alert alert-warning mb-0">
                <h6 class="fw-bold">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Masih Terdapat Kebutuhan yang Belum Terpenuhi
                </h6>

                Petugas perlu mempertimbangkan pengadaan tambahan
                atau mencari gudang lain untuk memenuhi kebutuhan.
            </div>

        @endif

        <div class="text-muted mt-3">
            <small>
                <i class="bi bi-shield-check"></i>
                Hasil matching merupakan rekomendasi sistem.
                Stok gudang tidak berubah sebelum proses distribusi
                dilakukan dan dikonfirmasi.
            </small>
        </div>

    </div>
</div>

@endif

@endsection
