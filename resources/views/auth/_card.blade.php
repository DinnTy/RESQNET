{{--
    Kartu masuk & daftar dengan panel geser (tema Siaga).
    Dipakai oleh auth/login.blade.php ($mode = 'login')
    dan auth/register.blade.php ($mode = 'register').

    Kedua form tetap mengirim ke route bawaan project:
    - Masuk  : POST route('login.process')   field: login, password
    - Daftar : POST route('register.store')  field: name, username, email, phone, password, password_confirmation
--}}
@php $isRegister = $mode === 'register'; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isRegister ? 'Daftar' : 'Masuk' }} | RESQNET</title>
    <link rel="icon" type="image/png" href="{{ asset('images/LOGORQ.PNG') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=JetBrains+Mono:wght@400;500;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/resqnet.css') }}?v={{ @filemtime(public_path('css/resqnet.css')) }}" rel="stylesheet">
</head>
<body class="rq rq-auth" data-wipe>

<div class="rq-auth-card {{ $isRegister ? 'is-reg' : '' }}" id="rqAuth"
     data-login-url="{{ route('login') }}" data-register-url="{{ route('register') }}">

    {{-- ================= PANEL MASUK ================= --}}
    <section class="rq-pane rq-pane--login" aria-labelledby="loginTitle" @if($isRegister) aria-hidden="true" @endif>
        <h2 class="rq-auth-h rq-display" id="loginTitle">Selamat <span class="rq-mark">datang</span></h2>
        <p class="rq-auth-s">Silakan masuk ke akun RESQNET Anda.</p>

        @if(session('success'))
            <div class="rq-auth-ok" role="status">{{ session('success') }}</div>
        @endif

        @if(!$isRegister && $errors->any())
            <div class="rq-auth-err" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf
            <label class="rq-field">
                <span>Username atau Email</span>
                <input class="rq-input @if(!$isRegister && $errors->has('login')) is-invalid @endif"
                       type="text" name="login" value="{{ $isRegister ? '' : old('login') }}"
                       autocomplete="username" required @if(!$isRegister) autofocus @endif>
            </label>

           <label class="rq-field">
    <span>Password</span>
    <div class="rq-password-wrap">
        <input class="rq-input" id="loginPassword" type="password"
               name="password" autocomplete="current-password" required>
        <button type="button" class="rq-password-toggle"
                data-toggle-password="loginPassword"
                aria-label="Lihat password"
                aria-pressed="false">
            <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                <path d="M9.9 5.2A11 11 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.3 3.8-.8"/>
            </svg>
        </button>
    </div>
</label>

<div class="rq-forgot">
    <a href="{{ route('password.request') }}">Lupa password?</a>
</div>

            <button type="submit" class="rq-btn rq-btn--or rq-btn--block" style="margin-top:22px">
                Masuk ke RESQNET
            </button>
        </form>

        <a class="rq-auth-back" href="{{ route('home') }}">← Kembali ke halaman utama</a>
    </section>

    {{-- ================= PANEL DAFTAR ================= --}}
    <section class="rq-pane rq-pane--reg" aria-labelledby="registerTitle" @unless($isRegister) aria-hidden="true" @endunless>
        <h2 class="rq-auth-h rq-display" id="registerTitle">Buat akun <span class="rq-mark">RESQNET</span></h2>
        <p class="rq-auth-s">Daftar sebagai masyarakat untuk melaporkan bencana dan mengajukan bantuan.</p>

        @if($isRegister && $errors->any())
            <div class="rq-auth-err" role="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
            @csrf
            <div class="rq-row2">
                <label class="rq-field">
                    <span>Nama Lengkap</span>
                    <input class="rq-input @if($isRegister && $errors->has('name')) is-invalid @endif"
                           type="text" name="name" value="{{ $isRegister ? old('name') : '' }}"
                           autocomplete="name" required @if($isRegister) autofocus @endif>
                </label>
                <label class="rq-field">
                    <span>Username</span>
                    <input class="rq-input @if($isRegister && $errors->has('username')) is-invalid @endif"
                           type="text" name="username" value="{{ $isRegister ? old('username') : '' }}"
                           maxlength="50" autocomplete="username" required>
                </label>
            </div>

            <div class="rq-row2">
                <label class="rq-field">
                    <span>Email</span>
                    <input class="rq-input @if($isRegister && $errors->has('email')) is-invalid @endif"
                           type="email" name="email" value="{{ $isRegister ? old('email') : '' }}"
                           autocomplete="email" required>
                </label>
                <label class="rq-field">
                    <span>Nomor Telepon</span>
                    <input class="rq-input @if($isRegister && $errors->has('phone')) is-invalid @endif"
                           type="tel" name="phone" value="{{ $isRegister ? old('phone') : '' }}"
                           maxlength="20" autocomplete="tel">
                </label>
            </div>

            <div class="rq-row2">
                <label class="rq-field">
                    <span>Password</span>
                    <input class="rq-input @if($isRegister && $errors->has('password')) is-invalid @endif"
                           type="password" name="password" minlength="8" autocomplete="new-password" required>
                </label>
                <label class="rq-field">
                    <span>Konfirmasi Password</span>
                    <input class="rq-input" type="password" name="password_confirmation"
                           minlength="8" autocomplete="new-password" required>
                </label>
            </div>

            <button type="submit" class="rq-btn rq-btn--or rq-btn--block" style="margin-top:20px">
                Daftar sekarang
            </button>
        </form>

        <a class="rq-auth-back" href="{{ route('home') }}">← Kembali ke halaman utama</a>
    </section>

    {{-- ================= PANEL GESER ================= --}}
    <div class="rq-ovl" id="rqOvl">
        <div class="rq-ot rq-ot--l">
            <span class="rq-wm">RESQNET</span>
            <h3 class="rq-display">Connecting aid.<br>Strengthening response.</h3>
            <p>Platform digital terintegrasi untuk koordinasi laporan bencana, kebutuhan masyarakat, persediaan bantuan, dan distribusi.</p>
            <a class="rq-btn rq-btn--or" href="{{ route('register') }}" data-auth-switch="register" data-no-wipe>Buat akun</a>
        </div>
        <div class="rq-ot rq-ot--r">
            <span class="rq-wm">RESQNET</span>
            <h3 class="rq-display">Bergabung<br>bersama RESQNET.</h3>
            <p>Sudah punya akun? Masuk untuk memantau laporan bencana dan permintaan bantuan Anda.</p>
            <a class="rq-btn rq-btn--or" href="{{ route('login') }}" data-auth-switch="login" data-no-wipe>Masuk</a>
        </div>
    </div>
</div>

<script src="{{ asset('js/resqnet.js') }}?v={{ @filemtime(public_path('js/resqnet.js')) }}"></script>
<script>
    (function () {
        var card = document.getElementById('rqAuth'), ovl = document.getElementById('rqOvl'), t;
        RQ.shapes(document.body, [['radar', 'k', '4%', '8%', 130, 18], ['warn', 'y', '90%', '78%', 64, 22], ['seis', 'o', '38%', '90%', 240, 10], ['cross', 'k', '92%', '12%', 44, 14]]);
        RQ.shapes(ovl, [['radar', 'w', '58%', '52%', 230, 8]]);

        // Pindah panel tanpa memuat ulang halaman, lalu URL ikut berubah.
        // Jika form daftar gagal validasi, Laravel kembali ke URL /register
        // sehingga panel daftar langsung terbuka bersama pesan error.
        document.querySelectorAll('[data-auth-switch]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var toRegister = link.getAttribute('data-auth-switch') === 'register';
                ovl.classList.remove('go');
                void ovl.offsetWidth;
                ovl.classList.add('go');
                clearTimeout(t);
                t = setTimeout(function () { ovl.classList.remove('go'); }, 1300);
                card.classList.toggle('is-reg', toRegister);
                document.querySelector('.rq-pane--login').setAttribute('aria-hidden', toRegister ? 'true' : 'false');
                document.querySelector('.rq-pane--reg').setAttribute('aria-hidden', toRegister ? 'false' : 'true');
                document.title = (toRegister ? 'Daftar' : 'Masuk') + ' | RESQNET';
                try { history.replaceState(null, '', toRegister ? card.dataset.registerUrl : card.dataset.loginUrl); } catch (err) {}
                setTimeout(function () {
                    var first = document.querySelector((toRegister ? '.rq-pane--reg' : '.rq-pane--login') + ' input:not([type=hidden])');
                    if (first) first.focus({ preventScroll: true });
                }, 700);
            });
        });
    })();
</script>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(function(button) {
    button.addEventListener('click', function() {
        const input = document.getElementById(
            button.getAttribute('data-toggle-password')
        );

        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';

        button.classList.toggle('is-visible', visible);
        button.setAttribute('aria-pressed', visible ? 'true' : 'false');
        button.setAttribute(
            'aria-label',
            visible ? 'Sembunyikan password' : 'Lihat password'
        );
    });
});
</script>
</body>
</html>
