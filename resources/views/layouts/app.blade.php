
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'RESQNET')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/LOGORQ.PNG') }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=JetBrains+Mono:wght@400;500;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">

    {{-- CSS RESQNET --}}
    <link href="{{ asset('css/resqnet.css') }}?v={{ @filemtime(public_path('css/resqnet.css')) }}" rel="stylesheet">

    @stack('styles')
</head>

<body class="rq rq-app" data-wipe>

@php
    $user = Auth::user();
    $role = $user?->role;

    // Menu sidebar berdasarkan role
    $menus = [];

    if ($role === 'masyarakat') {
        $menus = [
            ['dashboard', 'Dashboard', 'bi-grid-1x2', 'dashboard'],
            ['reports.create', 'Lapor Bencana', 'bi-exclamation-triangle', 'reports.create'],
            ['reports.index', 'Laporan Saya', 'bi-card-list', ['reports.index', 'reports.show']],
            ['aid-requests.create', 'Ajukan Bantuan', 'bi-box-seam', 'aid-requests.create'],
            ['aid-requests.index', 'Permintaan Saya', 'bi-inboxes', ['aid-requests.index', 'aid-requests.show']],
            ['distributions.index', 'Distribusi Bantuan', 'bi-truck', 'distributions.*'],
        ];

    } elseif (in_array($role, ['admin', 'petugas'])) {
        $menus = [
            ['dashboard', 'Dashboard', 'bi-grid-1x2', 'dashboard'],
            ['reports.index', 'Laporan Bencana', 'bi-exclamation-triangle', 'reports.*'],
            ['aid-requests.index', 'Permintaan Bantuan', 'bi-box-seam', 'aid-requests.*'],
            ['map', 'Peta GIS', 'bi-geo-alt', 'map'],
            ['distributions.index', 'Distribusi Bantuan', 'bi-truck', 'distributions.*'],
            ['warehouses.index', 'Gudang', 'bi-house-door', 'warehouses.*'],
            ['aid-items.index', 'Data Barang', 'bi-list-ul', 'aid-items.*'],
            ['aid-stocks.index', 'Stok Bantuan', 'bi-stack', 'aid-stocks.*'],
        ];

        // Menu khusus Admin
        if ($role === 'admin') {
            $menus[] = [
                'disaster-categories.index',
                'Kelola Kategori Bencana',
                'bi-tags',
                'disaster-categories.*'
            ];
        }
    }

    $roleLabel = [
        'admin' => 'Admin',
        'petugas' => 'Petugas',
        'masyarakat' => 'Masyarakat',
    ][$role] ?? ucfirst((string) $role);

    $pageTitle = trim(str_replace(
        ['- RESQNET', '| RESQNET'],
        '',
        $__env->yieldContent('title', 'RESQNET')
    ));

    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $notifications = $user
        ? $user->notifications()->latest()->take(8)->get()
        : collect();
@endphp

{{-- SIDEBAR --}}
<aside class="rq-side" id="rqSide" aria-label="Menu utama">

    <a class="rq-brand" href="{{ route('dashboard') }}">
        <img src="{{ asset('images/LOGORQ.PNG') }}" alt="">
        <span class="rq-wm">RESQNET</span>
    </a>

    <div class="rq-side__sub">
        Disaster Response<br>
        Management System
    </div>

    <nav>
        @foreach($menus as [$routeName, $label, $icon, $pattern])
            @php
                $active = request()->routeIs(...(array) $pattern);
            @endphp

            <a href="{{ route($routeName) }}"
               class="rq-mi {{ $active ? 'is-on' : '' }}"
               @if($active) aria-current="page" @endif>

                <i class="bi {{ $icon }}"></i>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="rq-side__sp"></div>

    @auth
        <div class="rq-side__user">
            <span class="rq-side__ava">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </span>

            <div>
                <b>{{ $user->name }}</b>
                <small>{{ $roleLabel }}</small>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit" class="rq-mi">
                <i class="bi bi-box-arrow-left"></i>
                Keluar
            </button>
        </form>
    @endauth

</aside>

<div class="rq-scrim"></div>

{{-- KONTEN UTAMA --}}
<div class="rq-main">

    <header class="rq-top">

        <button class="rq-bell rq-top__menu"
                type="button"
                aria-controls="rqSide"
                aria-expanded="false"
                aria-label="Buka menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="rq-top__crumb">
            RESQNET / {{ $pageTitle }}
        </div>

        <div class="rq-top__sp"></div>

        <span class="rq-chip rq-chip--date">
            <i class="dot"></i>
            {{ now()->translatedFormat('l, d F Y') }}
        </span>

        @auth
            <span class="rq-chip rq-chip--role">
                {{ $roleLabel }}
            </span>

            {{-- NOTIFIKASI --}}
            <div class="dropdown">

                <button class="rq-bell"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Notifikasi">

                    <i class="bi bi-bell-fill"></i>

                    @if($unreadCount > 0)
                        <span class="rq-bell__n">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif

                </button>

                <div class="dropdown-menu dropdown-menu-end rq-notif">

                    <div class="rq-notif__h">
                        <i class="bi bi-bell me-2"></i>
                        Notifikasi
                    </div>

                    <div class="rq-notif__list">

                        @forelse($notifications as $notification)

                            <div class="rq-notif__item {{ $notification->unread() ? 'unread' : '' }}">

                                <b>
                                    {{ $notification->data['title'] ?? 'Notifikasi RESQNET' }}
                                </b>

                                <p>
                                    {{ $notification->data['message'] ?? '-' }}
                                </p>

                                <div class="rq-notif__meta">

                                    <span>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>

                                    <div class="rq-notif__acts">

                                        @if(!empty($notification->data['url']))
                                            <a href="{{ $notification->data['url'] }}">
                                                Buka
                                            </a>
                                        @endif

                                        @if($notification->unread())
                                            <form action="{{ route('notifications.read', $notification->id) }}"
                                                  method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit">
                                                    Tandai dibaca
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </div>
                            </div>

                        @empty

                            <div class="rq-notif__empty">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                Belum ada notifikasi.
                            </div>

                        @endforelse

                    </div>
                </div>
            </div>
        @endauth

    </header>

    {{-- ISI HALAMAN --}}
    <main class="rq-content">

        @if(session('success'))
            <div class="rq-flash rq-flash--ok" role="status">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button type="button" data-dismiss-flash aria-label="Tutup">×</button>
            </div>
        @endif

        @if(session('error'))
            <div class="rq-flash rq-flash--err" role="alert">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>{{ session('error') }}</span>
                <button type="button" data-dismiss-flash aria-label="Tutup">×</button>
            </div>
        @endif

        @yield('content')

    </main>

    <footer class="rq-appfoot">
        © {{ date('Y') }} RESQNET — Resilience &amp; Emergency Support Queue Network
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/resqnet.js') }}?v={{ @filemtime(public_path('js/resqnet.js')) }}"></script>

@stack('scripts')

</body>
</html>
