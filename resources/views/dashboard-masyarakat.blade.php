@extends('layouts.app')

@section('title', 'Dashboard Masyarakat | RESQNET')

@php
    $reportBadge = [
        'terverifikasi' => ['Terverifikasi', 'rq-badge--ok'],
        'ditolak' => ['Ditolak', 'rq-badge--danger'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'rq-badge--wait'],
    ];
    $requestBadge = [
        'menunggu' => ['Menunggu', 'rq-badge--wait'],
        'disetujui' => ['Disetujui', 'rq-badge--info'],
        'diproses' => ['Diproses', 'rq-badge--info'],
        'terpenuhi' => ['Terpenuhi', 'rq-badge--ok'],
        'ditolak' => ['Ditolak', 'rq-badge--danger'],
    ];
@endphp

@section('content')

<div class="rq-head rq-stagger">
    <div>
        <h1 class="rq-display">Dashboard <span class="rq-mark">Masyarakat</span></h1>
        <p>Selamat datang, {{ auth()->user()->name }}. Pantau laporan bencana dan permintaan bantuan Anda di sini.</p>
    </div>
    <div class="rq-head__acts">
        <a href="{{ route('reports.create') }}" class="rq-btn rq-btn--or"><i class="bi bi-exclamation-triangle"></i>Laporkan Bencana</a>
        <a href="{{ route('aid-requests.create') }}" class="rq-btn"><i class="bi bi-box-seam"></i>Ajukan Bantuan</a>
    </div>
</div>

<div class="rq-grid rq-grid--4 rq-stagger">
    @foreach([
        ['Laporan Saya', $myReports, 'var(--rd)', route('reports.index')],
        ['Laporan Menunggu', $myPendingReports, 'var(--yl)', route('reports.index')],
        ['Permintaan Bantuan', $myAidRequests, 'var(--or)', route('aid-requests.index')],
        ['Permintaan Menunggu', $myPendingAidRequests, 'var(--yl)', route('aid-requests.index')],
    ] as [$label, $value, $color, $url])
        <a href="{{ $url }}" class="rq-card rq-stat--accent" style="--c:{{ $color }};color:inherit;text-decoration:none" data-wipe-label="{{ $label }}">
            <span class="rq-stat__l">{{ $label }}</span>
            <div class="rq-stat__n" data-od="{{ (int) $value }}">{{ $value }}</div>
        </a>
    @endforeach
</div>

<div class="rq-grid rq-grid--2" style="margin-top:22px">

    <div class="rq-card rq-card--flat">
        <div class="rq-card__hd">
            <div>
                <h3 class="rq-card__t">Laporan Bencana Terbaru Saya</h3>
                <span class="rq-card__s">5 laporan terakhir</span>
            </div>
            <a class="rq-link" href="{{ route('reports.index') }}">Semua <i class="bi bi-arrow-right"></i></a>
        </div>

        @forelse($latestReports as $report)
            @php [$label, $cls] = $reportBadge[$report->status] ?? [ucfirst(str_replace('_', ' ', $report->status)), 'rq-badge--mut']; @endphp
            <div class="rq-list__it">
                <div>
                    <b>{{ $report->title ?? 'Laporan Bencana' }}</b>
                    <small>{{ $report->category->name ?? '-' }} — {{ $report->location->name ?? '-' }}</small>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end">
                    <span class="rq-badge {{ $cls }}">{{ $label }}</span>
                    <a class="rq-link" href="{{ route('reports.show', $report->id) }}" aria-label="Detail laporan"><i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        @empty
            <div class="rq-empty">
                <i class="bi bi-clipboard"></i>
                Belum ada laporan bencana.
                <div style="margin-top:14px"><a class="rq-btn rq-btn--or rq-btn--sm" href="{{ route('reports.create') }}">Buat laporan pertama</a></div>
            </div>
        @endforelse
    </div>

    <div class="rq-card rq-card--flat">
        <div class="rq-card__hd">
            <div>
                <h3 class="rq-card__t">Permintaan Bantuan Terbaru Saya</h3>
                <span class="rq-card__s">5 permintaan terakhir</span>
            </div>
            <a class="rq-link" href="{{ route('aid-requests.index') }}">Semua <i class="bi bi-arrow-right"></i></a>
        </div>

        @forelse($latestAidRequests as $request)
            @php [$label, $cls] = $requestBadge[$request->status] ?? [ucfirst(str_replace('_', ' ', $request->status)), 'rq-badge--mut']; @endphp
            <div class="rq-list__it">
                <div>
                    <b>{{ $request->disasterReport->title ?? 'Permintaan Bantuan' }}</b>
                    <small>{{ $request->created_at?->format('d-m-Y H:i') ?? 'Tanggal tidak tersedia' }}</small>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end">
                    <span class="rq-badge {{ $cls }}">{{ $label }}</span>
                    <a class="rq-link" href="{{ route('aid-requests.show', $request->id) }}" aria-label="Detail permintaan"><i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        @empty
            <div class="rq-empty">
                <i class="bi bi-box-seam"></i>
                Belum ada permintaan bantuan.
                <div style="margin-top:6px;font-size:13px">Permintaan dapat diajukan setelah laporan Anda terverifikasi.</div>
            </div>
        @endforelse
    </div>

</div>

@endsection

@push('scripts')
<script>
    RQ.shapes(document.querySelector('.rq-content'), [['radar', 'k', '92%', '72%', 140, 16], ['seis', 'o', '40%', '96%', 240, 10], ['warn', 'y', '95%', '14%', 54, 18]]);
</script>
@endpush
