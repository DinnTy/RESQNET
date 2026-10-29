<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>RESQNET — Lapor Bencana, Bantuan Bergerak</title>
    <meta name="description" content="RESQNET: platform pelaporan bencana dan koordinasi distribusi bantuan yang menghubungkan masyarakat, petugas, dan admin.">
    <link rel="icon" type="image/png" href="{{ asset('images/LOGORQ.PNG') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=JetBrains+Mono:wght@400;500;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/resqnet.css') }}?v={{ @filemtime(public_path('css/resqnet.css')) }}" rel="stylesheet">
</head>
<body class="rq" data-wipe>

@php
    $user = Auth::user();
    $role = $user?->role;
    $isStaff = in_array($role, ['admin', 'petugas']);

    // Tujuan tombol utama menyesuaikan status login
    $reportUrl = $user ? route('reports.create') : route('login');

    // Menu platform berdasarkan role (tamu diarahkan ke halaman masuk)
    $platformMenu = [
        ['Dashboard Monitoring', 'Ringkasan laporan, permintaan, stok, dan distribusi bantuan.', 'dashboard', null],
        ['Laporan Bencana', 'Kirim laporan kejadian dan pantau status verifikasinya.', 'reports.index', null],
        ['Permintaan Bantuan', 'Ajukan dan pantau kebutuhan bantuan untuk laporan terverifikasi.', 'aid-requests.index', null],
        ['Distribusi Bantuan', 'Lacak penyaluran bantuan lengkap dengan kode pelacakan.', 'distributions.index', null],
        ['Peta GIS', 'Persebaran bencana, gudang, dan rekomendasi gudang terdekat.', 'map', 'staff'],
        ['Manajemen Gudang', 'Kelola lokasi dan koordinat gudang bantuan.', 'warehouses.index', 'staff'],
        ['Data Barang Bantuan', 'Kelola katalog barang bantuan RESQNET.', 'aid-items.index', 'staff'],
        ['Data Stok Bantuan', 'Kelola persediaan barang di setiap gudang.', 'aid-stocks.index', 'staff'],
    ];
    $platformMenu = array_values(array_filter($platformMenu, function ($m) use ($user, $isStaff) {
        return $m[3] !== 'staff' || !$user || $isStaff;
    }));
@endphp

{{-- ================= PRELOADER ================= --}}
<div id="rq-pre" aria-hidden="true">
    <i class="rq-pre__b" style="--c:#121417"></i>
    <i class="rq-pre__b" style="--c:#ff5a1f"></i>
    <div class="rq-pre__b rq-pre__top">
        <div class="rq-pre__w">
            @foreach(str_split('RESQNET') as $i => $letter)
                <span style="--i:{{ $i }}">{{ $letter }}</span>
            @endforeach
        </div>
        <p class="rq-pre__t">Menghubungkan laporan warga, gudang, dan tim penyalur bantuan</p>
        <div class="rq-pre__f">
            <div class="rq-pre__bar"><i></i></div>
            <span class="rq-pre__n">0%</span>
        </div>
    </div>
</div>
<div id="rq-progress" aria-hidden="true"><i></i></div>

{{-- ================= NAVIGASI ================= --}}
<nav class="rq-nav" aria-label="Navigasi utama">
    <a class="rq-brand" href="{{ route('home') }}">
        <img src="{{ asset('images/LOGORQ.PNG') }}" alt="">
        <span class="rq-wm">RESQNET</span>
    </a>
    <div class="rq-nav__links">
        <a class="rq-btn rq-btn--ghost" href="#cara">Cara kerja</a>
        @auth
            <a class="rq-btn rq-btn--ghost" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="rq-btn rq-btn--or" href="{{ route('reports.create') }}">Laporkan</a>
        @else
            <a class="rq-btn rq-btn--ghost" href="{{ route('login') }}">Masuk</a>
            <a class="rq-btn rq-btn--or" href="{{ route('register') }}">Daftar</a>
        @endauth
    </div>
</nav>

{{-- ================= HERO SINEMATIK ================= --}}
<section class="rq-cin" id="top">
    <div class="rq-cin__grid"></div>
    <div class="rq-radar"></div>
    <div class="rq-pnls" id="rqPanels"></div>

    {{-- Adegan 1: laporan sedang diketik --}}
    <div class="rq-sc" id="scSearch">
        <div class="rq-sbx">
            <div class="rq-bar">
                <div class="rq-bar__ip"><span id="rqTyped"></span></div>
                <div class="rq-bar__btn" id="rqSend">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Adegan 2: logo --}}
    <div class="rq-sc" id="scLogo">
        <div class="rq-lw">
            <img src="{{ asset('images/LOGORQ.PNG') }}" alt="RESQNET">
            <span class="rq-lw__t" id="rqLogoText"></span>
        </div>
    </div>

    {{-- Adegan 3: kata kinetik --}}
    <div class="rq-sc" id="scKinetic">
        <h2 class="rq-kin rq-display">
            <span class="rq-wd">Cukup</span>
            <span class="rq-wd"><span class="rq-mark">laporkan</span></span>
            <span class="rq-wd">kejadiannya</span>
        </h2>
        <svg class="rq-und" viewBox="0 0 400 60" fill="none" aria-hidden="true"><path pathLength="1" d="M30 34 L370 34"/></svg>
    </div>

    {{-- Adegan 4: tombol kirim --}}
    <div class="rq-sc" id="scCta">
        <div class="rq-cta" id="rqCtaDemo">Kirim laporan</div>
        <svg class="rq-cur" id="rqCursorDemo" viewBox="0 0 24 24" width="40" height="40" aria-hidden="true"><path d="M5 3l14 8-6 1.8L10 19z" fill="#fff" stroke="#121417" stroke-width="1.4" stroke-linejoin="round"/></svg>
    </div>

    {{-- Mockup dashboard (angka asli dari database) --}}
    <div class="rq-win rq-mock" id="rqMock" aria-hidden="true">
        <div class="rq-win__tb"><i></i><i></i><i></i><span>resqnet / dashboard</span></div>
        <div class="rq-mock__dg">
            <div class="rq-mock__sd"><b>RESQNET</b><u class="on"></u><u></u><u></u><u></u><u></u></div>
            <div class="rq-mock__ct">
                <h4>Dashboard Monitoring</h4>
                <p>Ringkasan aktivitas dan kondisi bantuan</p>
                <div class="rq-mock__kr">
                    <div><small>Laporan</small><b>{{ number_format($stats['total_reports'], 0, ',', '.') }}</b></div>
                    <div><small>Permintaan</small><b>{{ number_format($stats['aid_requests'], 0, ',', '.') }}</b></div>
                    <div><small>Stok tersedia</small><b>{{ number_format($stats['available_stock'], 0, ',', '.') }}</b></div>
                    <div><small>Distribusi</small><b>{{ number_format($stats['distributions'], 0, ',', '.') }}</b></div>
                </div>
                <div class="rq-mock__ch" id="rqMockChart"></div>
            </div>
        </div>
    </div>

    <div class="rq-fin">
        <h1 class="rq-display"><span id="rqFin1"></span><br><span class="rq-mark" id="rqFin2"></span></h1>
    </div>

    <div class="rq-fb">
        <a class="rq-btn rq-btn--or" href="{{ $reportUrl }}">Laporkan bencana</a>
        <a class="rq-btn" href="#cara">Lihat cara kerja</a>
        @auth
            <a class="rq-btn" href="{{ route('dashboard') }}">Buka dashboard</a>
        @else
            <a class="rq-btn" href="{{ route('login') }}">Masuk</a>
            <a class="rq-btn" href="{{ route('register') }}">Buat akun</a>
        @endauth
    </div>

    <button class="rq-skip" id="rqSkip" type="button">Lewati ›</button>

    <div class="rq-hud" aria-hidden="true">
        <i class="c c1"></i><i class="c c2"></i><i class="c c3"></i><i class="c c4"></i>
        <div class="rq-hud__s"><i></i><span>Siaga aktif</span><span>03°48′ LS 102°16′ BT</span><b data-clock>--:--:-- WIB</b></div>
    </div>
</section>

<div data-mq="Lapor|Verifikasi|Salurkan|Pantau" data-mod=""></div>

{{-- ================= CARA KERJA ================= --}}
<section class="rq-sec rq-sec--grid" id="cara">
    <h2 class="rq-h2 rq-display" style="text-align:center">
        <span id="rqA1"></span><br><span class="rq-mark" id="rqA2"></span><br><span id="rqA3"></span>
    </h2>
    <div class="rq-steps">
        <div class="rq-step rq-rv" data-n="01" style="--c:var(--rd)">
            <b>01</b>
            <h3 class="rq-display">Lapor</h3>
            <p>Kirim lokasi, jenis bencana, tingkat keparahan, dan foto bukti dalam hitungan detik.</p>
        </div>
        <div class="rq-step rq-rv" data-n="02" style="--c:var(--yl);transition-delay:.12s">
            <b>02</b>
            <h3 class="rq-display">Verifikasi</h3>
            <p>Petugas memeriksa laporan dan memetakannya sebelum bantuan diajukan.</p>
        </div>
        <div class="rq-step rq-rv" data-n="03" style="--c:var(--ok);transition-delay:.24s">
            <b>03</b>
            <h3 class="rq-display">Salurkan</h3>
            <p>Gudang terdekat dengan stok tersedia direkomendasikan, lalu bantuan dilacak sampai diterima.</p>
        </div>
    </div>
</section>

<div data-mq="Satu laporan|Satu jaringan|Bantuan tepat sasaran" data-mod="rq-mq--ink rq-mq--rev"></div>

{{-- ================= SOROTAN DATA ================= --}}
<section class="rq-sec rq-sec--dark" id="sorotan">
    <div class="rq-story-wrap">
        <div>
            <h2 class="rq-h2 rq-display"><span id="rqW1"></span><br><span class="rq-mark" id="rqW2"></span></h2>
            <p class="rq-lead rq-rv">Ringkasan laporan, permintaan, stok, dan distribusi bantuan yang tercatat di RESQNET saat ini.</p>
            <p class="rq-rv" style="margin-top:14px;font:500 12.5px var(--mono);color:#a9a7a1">Data langsung dari database RESQNET. Ketuk kartu untuk berpindah.</p>
        </div>
        <div class="rq-story" id="rqStory" role="button" tabindex="0" aria-label="Sorotan data RESQNET, tekan untuk slide berikutnya"></div>
    </div>
</section>

<div data-mq="Banjir|Longsor|Gempa|Kebakaran hutan|Tsunami|Laporkan sekarang" data-mod="rq-mq--or"></div>

{{-- ================= AJAKAN MELAPOR ================= --}}
<section class="rq-sec rq-sec--grid" id="lapor">
    <div class="rq-ctabox">
        <h2 class="rq-h2 rq-display">Laporkan<br><span class="rq-mark">kejadian.</span></h2>
        <p class="rq-lead">Laporan diteruskan ke petugas untuk diverifikasi, lalu bantuan disalurkan dari gudang terdekat yang memiliki stok.</p>

        <ul class="rq-checklist">
            <li>Pilih kategori dan tingkat keparahan bencana</li>
            <li>Tandai titik lokasi langsung di peta</li>
            <li>Lampirkan foto bukti (opsional)</li>
            <li>Pantau status laporan dan distribusinya</li>
        </ul>

        <div class="rq-ctabox__acts">
            @auth
                <a class="rq-btn rq-btn--or" href="{{ route('reports.create') }}"><i class="bi bi-exclamation-triangle"></i>Buat laporan bencana</a>
                <a class="rq-btn" href="{{ route('aid-requests.create') }}"><i class="bi bi-box-seam"></i>Ajukan bantuan</a>
            @else
                <a class="rq-btn rq-btn--or" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>Masuk dan buat laporan</a>
                <a class="rq-btn" href="{{ route('register') }}"><i class="bi bi-person-plus"></i>Buat akun RESQNET</a>
            @endauth
        </div>
    </div>
</section>

<div data-mq="Dashboard|Peta GIS|Distribusi|Gudang|Stok" data-mod="rq-mq--paper rq-mq--rev"></div>

{{-- ================= MENU PLATFORM ================= --}}
<section class="rq-sec rq-sec--steel" id="menu">
    <h2 class="rq-h2 rq-display">Semua tampilan,<br><span class="rq-mark">satu platform.</span></h2>
    <p class="rq-lead">
        @auth
            Buka bagian RESQNET yang tersedia untuk akun {{ ucfirst($role) }} Anda.
        @else
            Masuk untuk membuka setiap bagian. Menu yang tampil menyesuaikan peran akun Anda.
        @endauth
    </p>
    <div class="rq-menu">
        @foreach($platformMenu as $i => [$title, $desc, $routeName])
            <a href="{{ $user ? route($routeName) : route('login') }}" class="rq-rv">
                <b>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</b>
                <i class="bi bi-arrow-right"></i>
                <h3 class="rq-display">{{ $title }}</h3>
                <p>{{ $desc }}</p>
            </a>
        @endforeach
    </div>
</section>

<div data-mq="RESQNET|Resilience &amp; Emergency Support Queue Network" data-mod=""></div>

{{-- ================= FOOTER ================= --}}
<footer class="rq-foot">
    <img src="{{ asset('images/LOGORQ.PNG') }}" alt="RESQNET">
    <span class="rq-wm">RESQNET</span>
    Resilience &amp; Emergency Support Queue Network<br>
    Disaster Response Management System · © {{ date('Y') }}
    <div class="rq-foot__big" aria-hidden="true">
        @foreach(str_split('RESQNET') as $i => $letter)
            <span style="--i:{{ $i }}">{{ $letter }}</span>
        @endforeach
    </div>
</footer>

<script>
    // Angka ringkasan dari HomeController (database)
    window.RQ_HOME = @json($stats);
</script>
<script src="{{ asset('js/resqnet.js') }}?v={{ @filemtime(public_path('js/resqnet.js')) }}"></script>
<script src="{{ asset('js/resqnet-home.js') }}?v={{ @filemtime(public_path('js/resqnet-home.js')) }}"></script>
</body>
</html>
