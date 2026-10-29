@extends('layouts.app')

@section('title', 'Daftar Laporan Bencana')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1">Laporan Bencana</h3>
            <p class="text-muted mb-0">
                Kelola dan pantau laporan bencana yang masuk ke RESQNET.
            </p>
        </div>

        <div class="d-flex gap-2">
            <span class="badge bg-primary px-3 py-2">
                Total: {{ $reports->total() }} Laporan
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Informasi Laporan</th>
                            <th>Pelapor</th>
                            <th>Lokasi</th>
                            <th>Keparahan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}</td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $report->title }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $report->category->name ?? 'Kategori tidak tersedia' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $report->user->name ?? '-' }}
                                </td>

                                <td>
                                    <div>{{ $report->location->name ?? '-' }}</div>
                                    <small class="text-muted">
                                        {{ $report->location->city ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    @php
                                        $severityClass = [
                                            'rendah' => 'success',
                                            'sedang' => 'warning',
                                            'tinggi' => 'danger',
                                            'kritis' => 'dark',
                                        ][$report->severity] ?? 'secondary';
                                    @endphp

                                    <span class="badge bg-{{ $severityClass }}">
                                        {{ ucfirst($report->severity) }}
                                    </span>
                                </td>

                                <td>
                                    @php
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

                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                   {{ $report->created_at ? $report->created_at->format('d/m/Y') : '-' }}
                                </td>

                                <td>
                                    <a href="{{ route('reports.show', $report->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Belum ada laporan bencana.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $reports->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
