@extends('layouts.app')

@section('title', 'Detail Laporan Bencana')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Detail Laporan Bencana</h3>
            <p class="text-muted mb-0">Informasi lengkap laporan masyarakat</p>
        </div>

        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @php
        $severityClass = [
            'rendah' => 'success',
            'sedang' => 'warning',
            'tinggi' => 'danger',
            'kritis' => 'dark',
        ][$report->severity] ?? 'secondary';

        $statusClass = [
            'menunggu_verifikasi' => 'warning',
            'terverifikasi' => 'success',
            'ditolak' => 'danger',
        ][$report->status] ?? 'secondary';

        $statusLabel = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
        ][$report->status] ?? ucfirst($report->status);
    @endphp

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <span class="text-muted small">Judul Laporan</span>
                            <h4 class="fw-bold mt-1">{{ $report->title }}</h4>
                        </div>

                        <span class="badge bg-{{ $statusClass }} px-3 py-2">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3">Informasi Laporan</h5>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Nama Pelapor</small>
                            <div class="fw-semibold">{{ $report->user->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Kategori Bencana</small>
                            <div class="fw-semibold">{{ $report->category->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Tingkat Keparahan</small>
                            <div>
                                <span class="badge bg-{{ $severityClass }}">
                                    {{ ucfirst($report->severity) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Tanggal Laporan</small>
                            <div class="fw-semibold">
                                {{ $report->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3">Deskripsi Kejadian</h5>
                    <p class="text-secondary" style="white-space: pre-line;">{{ $report->description }}</p>

                    @if($report->evidence)
                        <hr>
                        <h5 class="fw-bold mb-3">Foto Bukti</h5>

                        <a href="{{ asset('storage/' . $report->evidence) }}" target="_blank">
                            <img src="{{ asset('storage/' . $report->evidence) }}"
                                 alt="Foto bukti laporan"
                                 class="img-fluid rounded"
                                 style="max-width: 450px; max-height: 350px; object-fit: cover;">
                        </a>
                    @endif

                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-geo-alt text-danger"></i>
                        Informasi Lokasi
                    </h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Nama Lokasi</small>
                            <div class="fw-semibold">{{ $report->location->name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Alamat</small>
                            <div class="fw-semibold">{{ $report->location->address ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Kecamatan</small>
                            <div class="fw-semibold">{{ $report->location->district ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Kota/Kabupaten</small>
                            <div class="fw-semibold">{{ $report->location->city ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Provinsi</small>
                            <div class="fw-semibold">{{ $report->location->province ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Waktu Kejadian</small>
                            <div class="fw-semibold">
                                {{ $report->occurred_at ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Latitude</small>
                            <div class="fw-semibold">{{ $report->location->latitude ?? '-' }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Longitude</small>
                            <div class="fw-semibold">{{ $report->location->longitude ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            @if(in_array(Auth::user()->role, ['admin', 'petugas']))
            @if($report->status === 'terverifikasi')
    <a href="{{ route('aid-requests.create', ['report_id' => $report->id]) }}"
       class="btn btn-primary w-100 mb-3">
        <i class="bi bi-plus-circle"></i>
        Buat Permintaan Bantuan
    </a>
@endif
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-2">
                            <i class="bi bi-shield-check text-primary"></i>
                            Verifikasi Laporan
                        </h5>

                        <p class="text-muted small">
                            Periksa informasi laporan sebelum menentukan hasil verifikasi.
                        </p>

                        <div class="mb-3">
                            <small class="text-muted">Status Saat Ini</small>
                            <div class="mt-1">
                                <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        <hr>

                        <form action="{{ route('reports.update-status', $report->id) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="terverifikasi">

                            <button type="submit"
                                    class="btn btn-success w-100 mb-2"
                                    onclick="return confirm('Apakah kamu yakin ingin memverifikasi laporan ini?')"
                                    {{ $report->status === 'terverifikasi' ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle"></i>
                                Verifikasi Laporan
                            </button>
                        </form>

                        <form action="{{ route('reports.update-status', $report->id) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="ditolak">

                            <button type="submit"
                                    class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Apakah kamu yakin ingin menolak laporan ini?')"
                                    {{ $report->status === 'ditolak' ? 'disabled' : '' }}>
                                <i class="bi bi-x-circle"></i>
                                Tolak Laporan
                            </button>
                        </form>

                        @if($report->status !== 'menunggu_verifikasi')
                            <div class="alert alert-light border mt-3 mb-0 small">
                                Laporan ini sudah diproses. Status masih dapat diubah oleh Admin/Petugas.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold">Status Pemeriksaan</h5>
                        <span class="badge bg-{{ $statusClass }} px-3 py-2">
                            {{ $statusLabel }}
                        </span>
                        <p class="text-muted small mt-3 mb-0">
                            Status laporan akan diperbarui setelah diperiksa oleh Admin/Petugas.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
