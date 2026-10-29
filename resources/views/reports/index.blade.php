@extends('layouts.app')

@section('title', 'Laporan Bencana | RESQNET')

@php
    $severityBadge = [
        'rendah' => ['Rendah', 'rq-badge--ok'],
        'sedang' => ['Sedang', 'rq-badge--wait'],
        'tinggi' => ['Tinggi', 'rq-badge--info'],
        'kritis' => ['Kritis', 'rq-badge--danger'],
    ];
    $statusBadge = [
        'terverifikasi' => ['Terverifikasi', 'rq-badge--ok'],
        'ditolak' => ['Ditolak', 'rq-badge--danger'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'rq-badge--wait'],
    ];
    $isCommunity = auth()->user()->role === 'masyarakat';
@endphp

@section('content')

<div class="rq-head rq-stagger">
    <div>
        <h1 class="rq-display">Laporan <span class="rq-mark">{{ $isCommunity ? 'Saya' : 'Bencana' }}</span></h1>
        <p>
            @if($isCommunity)
                Pantau status laporan kejadian bencana yang Anda kirim.
            @else
                Pantau dan verifikasi seluruh laporan kejadian bencana.
            @endif
        </p>
    </div>
    <div class="rq-head__acts">
        <a href="{{ route('reports.create') }}" class="rq-btn rq-btn--or"><i class="bi bi-plus-lg"></i>Buat Laporan</a>
    </div>
</div>

<div class="rq-grid rq-grid--3 rq-stagger">
    <div class="rq-card rq-stat--accent" style="--c:var(--rd)">
        <span class="rq-stat__l">Total Laporan Ditampilkan</span>
        <div class="rq-stat__n rq-stat__n--sm" data-od="{{ $reports->total() }}">{{ $reports->total() }}</div>
    </div>
    <div class="rq-card rq-stat--accent" style="--c:var(--ink)">
        <span class="rq-stat__l">Halaman Saat Ini</span>
        <div class="rq-stat__n rq-stat__n--sm" data-od="{{ $reports->currentPage() }}">{{ $reports->currentPage() }}</div>
    </div>
    <div class="rq-card rq-stat--accent" style="--c:var(--or)">
        <span class="rq-stat__l">Jumlah Halaman</span>
        <div class="rq-stat__n rq-stat__n--sm" data-od="{{ $reports->lastPage() }}">{{ $reports->lastPage() }}</div>
    </div>
</div>

<div class="rq-card rq-card--flat" style="margin-top:20px">
    <div class="rq-card__hd">
        <h3 class="rq-card__t">Daftar Laporan Bencana</h3>
        <span class="rq-badge rq-badge--mut">{{ $reports->total() }} data laporan</span>
    </div>

    <div class="rq-tablewrap">
        <table class="rq-table" style="min-width:860px">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Laporan</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Pelapor</th>
                    <th>Keparahan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        [$sevLabel, $sevClass] = $severityBadge[$report->severity] ?? [ucfirst($report->severity), 'rq-badge--mut'];
                        [$stLabel, $stClass] = $statusBadge[$report->status] ?? [ucfirst(str_replace('_', ' ', $report->status)), 'rq-badge--mut'];
                    @endphp
                    <tr>
                        <td class="rq-code">{{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}</td>
                        <td class="rq-strong" style="max-width:240px">{{ $report->title }}</td>
                        <td>{{ $report->category->name ?? '-' }}</td>
                        <td><i class="bi bi-geo-alt"></i> {{ $report->location->name ?? '-' }}</td>
                        <td>{{ $report->user->name ?? '-' }}</td>
                        <td><span class="rq-badge {{ $sevClass }}">{{ $sevLabel }}</span></td>
                        <td><span class="rq-badge {{ $stClass }}">{{ $stLabel }}</span></td>
                        <td>
                            <a href="{{ route('reports.show', $report->id) }}" class="rq-link" data-wipe-label="Detail laporan">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="rq-empty">
                                <i class="bi bi-clipboard"></i>
                                <strong>Belum ada laporan</strong>
                                <div style="margin-top:6px">Laporan bencana yang dikirim akan tampil di sini.</div>
                                <div style="margin-top:14px"><a class="rq-btn rq-btn--or rq-btn--sm" href="{{ route('reports.create') }}">Buat laporan</a></div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
        <div class="rq-pager">
            {{ $reports->links() }}
        </div>
    @endif
</div>

@endsection
