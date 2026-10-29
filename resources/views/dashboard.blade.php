@extends('layouts.app')

@section('title', 'Dashboard | RESQNET')

@php
    $isAdmin = auth()->user()->role === 'admin';
    $isPetugas = auth()->user()->role === 'petugas';
    $isStaff = $isAdmin || $isPetugas;

    // Label & warna status (tema Siaga)
    $reportBadge = [
        'terverifikasi' => ['Terverifikasi', 'rq-badge--ok'],
        'ditolak' => ['Ditolak', 'rq-badge--danger'],
        'menunggu_verifikasi' => ['Menunggu Verifikasi', 'rq-badge--wait'],
    ];

    $distributionBadge = [
        'dialokasikan' => ['Dialokasikan', 'rq-badge--info'],
        'dikirim' => ['Dikirim', 'rq-badge--wait'],
        'diterima' => ['Diterima', 'rq-badge--ok'],
        'dibatalkan' => ['Dibatalkan', 'rq-badge--danger'],
    ];

    // Persentase untuk cincin progres
    $pct = fn ($value, $total) => $total > 0 ? min(1, $value / $total) : 0;

    $ring = function ($value, $total) use ($pct) {
        $p = $pct($value, $total);

        return [
            'offset' => number_format(138.2 * (1 - $p), 1, '.', ''),
            'label' => round($p * 100) . '%'
        ];
    };

    $summary = [
        ['Total Laporan Bencana', $totalReports, $verifiedReports . ' laporan telah terverifikasi', 'bi-exclamation-triangle', 'rq-ic--rd', route('reports.index')],
        ['Menunggu Verifikasi', $pendingReports, 'Laporan perlu diperiksa', 'bi-hourglass-split', 'rq-ic--yl', route('reports.index')],
        ['Permintaan Bantuan', $totalAidRequests, $pendingAidRequests . ' permintaan menunggu', 'bi-box-seam', '', route('aid-requests.index')],
        ['Permintaan Terpenuhi', $fulfilledAidRequests, 'Permintaan bantuan selesai', 'bi-check2-circle', 'rq-ic--ok', route('aid-requests.index')],
        ['Total Gudang', $totalWarehouses, $activeWarehouses . ' gudang aktif', 'bi-house-door', 'rq-ic--ink', route('warehouses.index')],
        ['Total Distribusi', $totalDistributions, 'Seluruh proses penyaluran bantuan', 'bi-truck', '', route('distributions.index')],
    ];

    $distributionCards = [
        ['Dialokasikan', $allocatedDistributions, 'var(--stl)'],
        ['Sedang Dikirim', $shippedDistributions, 'var(--yl)'],
        ['Telah Diterima', $receivedDistributions, 'var(--ok)'],
        ['Dibatalkan', $cancelledDistributions, 'var(--rd)'],
    ];

    $tickerText = implode(' ▲ ', [
        $verifiedReports . ' laporan terverifikasi',
        $pendingReports . ' menunggu verifikasi',
        $totalAidRequests . ' permintaan bantuan',
        $pendingAidRequests . ' permintaan menunggu',
        number_format($availableStock, 0, ',', '.') . ' stok tersedia',
        $totalDistributions . ' distribusi tercatat',
    ]);
@endphp

@section('content')

<div class="rq-head rq-stagger">
    <div>
        <h1 class="rq-display">Dashboard <span class="rq-mark">Monitoring</span></h1>
        <p>Ringkasan aktivitas dan kondisi bantuan RESQNET. Semua angka diambil langsung dari database.</p>
    </div>

    <div class="rq-head__acts">
        @if($isStaff)
            <a class="rq-btn rq-btn--sm" href="{{ route('map') }}">
                <i class="bi bi-geo-alt"></i>Peta GIS
            </a>

            <a class="rq-btn rq-btn--or rq-btn--sm" href="{{ route('distributions.create') }}">
                <i class="bi bi-plus-lg"></i>Buat Distribusi
            </a>
        @endif
    </div>
</div>

<div class="rq-ticker" data-ticker="{{ $tickerText }}"></div>

{{-- ================= RINGKASAN SISTEM ================= --}}
<div class="rq-h"><span>Ringkasan</span>Sistem</div>

<div class="rq-grid rq-grid--3 rq-stagger">
    @foreach($summary as [$label, $value, $foot, $icon, $iconClass, $url])
        <a href="{{ $url }}"
           class="rq-card"
           style="color:inherit;text-decoration:none"
           data-wipe-label="{{ $label }}">

            <div class="rq-stat__top">
                <span class="rq-stat__l">{{ $label }}</span>
                <span class="rq-ic {{ $iconClass }}">
                    <i class="bi {{ $icon }}"></i>
                </span>
            </div>

            <div class="rq-stat__n" data-od="{{ (int) $value }}">
                {{ number_format($value, 0, ',', '.') }}
            </div>

            <span class="rq-stat__f">{{ $foot }}</span>
        </a>
    @endforeach
</div>

{{-- ================= STATUS DISTRIBUSI ================= --}}
<div class="rq-h"><span>Status</span>Distribusi Bantuan</div>

<div class="rq-grid rq-grid--4 rq-stagger">
    @foreach($distributionCards as [$label, $value, $color])
        @php $r = $ring($value, $totalDistributions); @endphp

        <div class="rq-card rq-stat--accent" style="--c:{{ $color }}">
            <span class="rq-stat__l">{{ $label }}</span>

            <div class="rq-stat__n rq-stat__n--sm" data-od="{{ (int) $value }}">
                {{ $value }}
            </div>

            <div class="rq-ring" style="--o:{{ $r['offset'] }}">
                <svg viewBox="0 0 54 54">
                    <circle class="tr" cx="27" cy="27" r="22"/>
                    <circle class="pg" cx="27" cy="27" r="22"/>
                </svg>

                <b>{{ $r['label'] }}</b>
            </div>
        </div>
    @endforeach
</div>

{{-- ================= MONITORING STOK ================= --}}
<div class="rq-h"><span>Monitoring</span>Stok Bantuan</div>

<div class="rq-grid rq-grid--3 rq-stagger">
    @foreach([
        ['Total Stok', $totalStock, $totalStock, 'var(--ink)'],
        ['Stok Tersedia', $availableStock, $totalStock, 'var(--ok)'],
        ['Stok Dipesan', $reservedStock, $totalStock, 'var(--yl)'],
    ] as [$label, $value, $total, $color])

        @php $r = $ring($value, $total); @endphp

        <div class="rq-card rq-stat--accent" style="--c:{{ $color }}">
            <span class="rq-stat__l">{{ $label }}</span>

            <div class="rq-stat__n rq-stat__n--sm" data-od="{{ (int) $value }}">
                {{ number_format($value, 0, ',', '.') }}
            </div>

            <div class="rq-ring" style="--o:{{ $r['offset'] }}">
                <svg viewBox="0 0 54 54">
                    <circle class="tr" cx="27" cy="27" r="22"/>
                    <circle class="pg" cx="27" cy="27" r="22"/>
                </svg>

                <b>{{ $r['label'] }}</b>
            </div>
        </div>
    @endforeach
</div>

{{-- ================= ANALITIK ================= --}}
<div class="rq-h"><span>Analitik</span>dan Visualisasi Data</div>

<div class="rq-grid rq-grid--2">
    <div class="rq-card rq-card--flat">
        <h3 class="rq-card__t">Laporan Berdasarkan Kategori</h3>
        <span class="rq-card__s">Jumlah laporan untuk setiap kategori bencana</span>

        @if(count($reportChartValues))
            <div class="rq-chart">
                <canvas id="reportChart"
                        aria-label="Grafik laporan per kategori"
                        role="img"></canvas>
            </div>
        @else
            <div class="rq-empty">
                <i class="bi bi-pie-chart"></i>Belum ada data laporan.
            </div>
        @endif
    </div>

    <div class="rq-card rq-card--flat">
        <h3 class="rq-card__t">Status Distribusi</h3>
        <span class="rq-card__s">Visualisasi status penyaluran bantuan</span>

        <div class="rq-chart">
            <canvas id="distributionChart"
                    aria-label="Grafik status distribusi"
                    role="img"></canvas>
        </div>
    </div>
</div>

<div class="rq-card rq-card--flat" style="margin-top:16px">
    <h3 class="rq-card__t">Jumlah Stok Berdasarkan Barang</h3>
    <span class="rq-card__s">Perbandingan jumlah stok bantuan pada seluruh gudang</span>

    @if(count($stockChartValues))
        <div class="rq-chart" style="height:{{ max(180, count($stockChartValues) * 56 + 60) }}px">
            <canvas id="stockChart"
                    aria-label="Grafik stok per barang"
                    role="img"></canvas>
        </div>
    @else
        <div class="rq-empty">
            <i class="bi bi-stack"></i>Belum ada data stok.
        </div>
    @endif
</div>

{{-- ================= LAPORAN TERBARU ================= --}}
<div class="rq-card rq-card--flat" style="margin-top:16px">
    <div class="rq-card__hd">
        <div>
            <h3 class="rq-card__t">Laporan Bencana Terbaru</h3>
            <span class="rq-card__s">5 laporan terakhir yang masuk</span>
        </div>

        <a class="rq-link" href="{{ route('reports.index') }}">
            Semua laporan <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="rq-tablewrap">
        <table class="rq-table">
            <thead>
                <tr>
                    <th>Judul Laporan</th>
                    <th>Pelapor</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($latestReports as $report)
                    @php
                        [$label, $cls] = $reportBadge[$report->status]
                            ?? [
                                ucfirst(str_replace('_', ' ', $report->status)),
                                'rq-badge--mut'
                            ];
                    @endphp

                    <tr>
                        <td class="rq-strong">{{ $report->title }}</td>
                        <td>{{ $report->user->name ?? '-' }}</td>
                        <td>{{ $report->category->name ?? '-' }}</td>
                        <td>
                            <i class="bi bi-geo-alt"></i>
                            {{ $report->location->name ?? '-' }}
                        </td>
                        <td>
                            <span class="rq-badge {{ $cls }}">{{ $label }}</span>
                        </td>
                        <td>
                            <a class="rq-link" href="{{ route('reports.show', $report->id) }}">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="rq-empty">
                                <i class="bi bi-clipboard"></i>
                                Belum ada laporan bencana.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= DISTRIBUSI TERBARU ================= --}}
<div class="rq-card rq-card--flat" style="margin-top:16px">
    <div class="rq-card__hd">
        <div>
            <h3 class="rq-card__t">Distribusi Bantuan Terbaru</h3>
            <span class="rq-card__s">5 distribusi terakhir</span>
        </div>

        <a class="rq-link" href="{{ route('distributions.index') }}">
            Semua distribusi <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="rq-tablewrap">
        <table class="rq-table">
            <thead>
                <tr>
                    <th>Kode Tracking</th>
                    <th>Jenis Bantuan</th>
                    <th>Gudang</th>
                    <th>Tujuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($latestDistributions as $distribution)
                    @php
                        [$label, $cls] = $distributionBadge[$distribution->status]
                            ?? [
                                ucfirst($distribution->status),
                                'rq-badge--mut'
                            ];

                        $itemNames = $distribution->details
                            ->map(fn ($d) => $d->aidItem?->name)
                            ->filter()
                            ->unique()
                            ->implode(', ');
                    @endphp

                    <tr>
                        <td>
                            <span class="rq-code">
                                {{ $distribution->tracking_code ?? '-' }}
                            </span>
                        </td>

                        <td>
                            {{ $itemNames ?: ($distribution->aidRequest->item_name ?? '-') }}
                        </td>

                        <td>{{ $distribution->warehouse->name ?? '-' }}</td>

                        <td>
                            {{ $distribution->aidRequest->disasterReport->location->name ?? '-' }}
                        </td>

                        <td>
                            <span class="rq-badge {{ $cls }}">{{ $label }}</span>
                        </td>

                        <td>
                            <a class="rq-link" href="{{ route('distributions.show', $distribution->id) }}">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="rq-empty">
                                <i class="bi bi-truck"></i>
                                Belum ada distribusi bantuan.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    (function () {
        // Pita data berjalan
        var ticker = document.querySelector('[data-ticker]');

        if (ticker) {
            ticker.outerHTML = RQ.marquee(
                ticker.getAttribute('data-ticker'),
                'rq-mq--ink rq-ticker'
            );
        }

        RQ.shapes(
            document.querySelector('.rq-content'),
            [
                ['radar', 'k', '92%', '70%', 140, 16],
                ['seis', 'o', '40%', '96%', 240, 10],
                ['cross', 'k', '95%', '18%', 40, 14]
            ]
        );

        if (typeof Chart === 'undefined') return;

        var INK = '#121417';
        var OR = '#ff5a1f';
        var YL = '#ffc400';
        var RD = '#e5242b';
        var OK = '#1f9d55';
        var STL = '#2a2f36';

        Chart.defaults.font.family = "'Archivo', system-ui, sans-serif";
        Chart.defaults.font.weight = 600;
        Chart.defaults.color = INK;
        Chart.defaults.plugins.legend.labels.boxWidth = 14;
        Chart.defaults.plugins.tooltip.backgroundColor = INK;
        Chart.defaults.plugins.tooltip.cornerRadius = 4;

        var palette = [
            OR,
            YL,
            RD,
            OK,
            STL,
            '#8d8b85',
            '#ffa270',
            '#5b8def'
        ];

        var grid = {
            color: 'rgba(18,20,23,.12)'
        };

        // Data dari DashboardController
        var reportLabels = @json($reportChartLabels);
        var reportValues = @json($reportChartValues);

        var distributionLabels = @json($distributionChartLabels);
        var distributionValues = @json($distributionChartValues);

        var stockLabels = @json($stockChartLabels);
        var stockValues = @json($stockChartValues);

        var reportCanvas = document.getElementById('reportChart');

        if (reportCanvas) {
            new Chart(reportCanvas, {
                type: 'doughnut',

                data: {
                    labels: reportLabels,
                    datasets: [{
                        data: reportValues,
                        backgroundColor: palette,
                        borderColor: INK,
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },

                options: {
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        var distributionCanvas = document.getElementById('distributionChart');

        if (distributionCanvas) {
            new Chart(distributionCanvas, {
                type: 'bar',

                data: {
                    labels: distributionLabels,
                    datasets: [{
                        data: distributionValues,
                        backgroundColor: ['#8d8b85', YL, OK, RD],
                        borderColor: INK,
                        borderWidth: 2,
                        borderRadius: 3,
                        borderSkipped: 'bottom'
                    }]
                },

                options: {
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false
                        }
                    },

                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: grid
                        },

                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        var stockCanvas = document.getElementById('stockChart');

        if (stockCanvas) {
            new Chart(stockCanvas, {
                type: 'bar',

                data: {
                    labels: stockLabels,
                    datasets: [{
                        data: stockValues,
                        backgroundColor: palette,
                        borderColor: INK,
                        borderWidth: 2,
                        borderRadius: 3,
                        borderSkipped: 'left'
                    }]
                },

                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false
                        }
                    },

                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: grid
                        },

                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    })();
</script>
@endpush