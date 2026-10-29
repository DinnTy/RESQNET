<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password | RESQNET</title>
    <link href="{{ asset('css/resqnet.css') }}" rel="stylesheet">
</head>
<body

 class="rq rq-auth">
    <div class="rq-auth-card" style="max-width:480px;margin:50px auto;padding:35px">
        <h2 class="rq-auth-h rq-display">Lupa <span class="rq-mark">Password?</span></h2>
        <p class="rq-auth-s">Masukkan email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang password Anda.</p>

        @if(session('status'))
            <div class="rq-auth-ok">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="rq-auth-err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <label class="rq-field">
                <span>Email terdaftar</span>
                <input class="rq-input" type="email" name="email"
                       value="{{ old('email') }}" required autofocus>
            </label>

            <button class="rq-btn rq-btn--or rq-btn--block" type="submit" style="margin-top:20px">
                Kirim tautan reset
            </button>
        </form>

        <a class="rq-auth-back" href="{{ route('login') }}">← Kembali ke login</a>
    </div>
</body>
</html>