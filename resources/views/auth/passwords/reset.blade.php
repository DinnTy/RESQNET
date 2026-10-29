<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Password Baru | RESQNET</title>
    <link href="{{ asset('css/resqnet.css') }}" rel="stylesheet">
</head>
<body class="rq rq-auth">
    <div class="rq-auth-card" style="max-width:480px;margin:50px auto;padding:35px">
        <h2 class="rq-auth-h rq-display">Atur Password <span class="rq-mark">Baru</span></h2>
        <p class="rq-auth-s">Buat password baru untuk akun RESQNET Anda.</p>

        @if($errors->any())
            <div class="rq-auth-err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <label class="rq-field">
                <span>Email</span>
                <input class="rq-input" type="email" name="email"
                       value="{{ old('email', $email) }}" required>
            </label>

            <label class="rq-field">
                <span>Password baru</span>
                <input class="rq-input" type="password" name="password"
                       minlength="8" required>
            </label>

            <label class="rq-field">
                <span>Konfirmasi password</span>
                <input class="rq-input" type="password"
                       name="password_confirmation" minlength="8" required>
            </label>

            <button class="rq-btn rq-btn--or rq-btn--block" type="submit" style="margin-top:20px">
                Simpan password baru
            </button>
        </form>
    </div>
</body>
</html>