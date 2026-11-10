<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi | Sahabat Sekolah</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="login-page">
<div class="login-experience">
    <section class="login-visual" aria-label="Sahabat Sekolah Bukittinggi">
        <div class="visual-overlay"></div>
        <div class="visual-topbar">
            <a class="visual-brand" href="{{ route('login') }}"><span class="visual-logo"><i data-lucide="heart-handshake"></i></span><span><strong>Sahabat<br>Sekolah</strong><small>Sekolah Aman,<br>Semua Nyaman</small></span></a>
            <div class="visual-spark">✦</div>
        </div>
        <div class="visual-copy"><span class="visual-kicker">KOTA BUKITTINGGI</span><h2>Pemulihan<br>Akses Akun</h2><div class="yellow-stroke"></div><p>“Keamanan data dan<br>layanan sekolah yang<br>tetap terlindungi.”</p></div>
        <div class="visual-children"><span class="child child-one"></span><span class="child child-two"></span><span class="child child-three"></span><span class="child child-four"></span></div>
        <div class="visual-wave"></div>
        <div class="visual-values"><span><i data-lucide="shield-check"></i>Aman</span><span><i data-lucide="users"></i>Peduli</span><span><i data-lucide="leaf"></i>Saling Mendukung</span><span><i data-lucide="graduation-cap"></i>Sekolah Lebih Baik</span></div>
    </section>
    <section class="login-panel">
        <div class="login-panel-inner">
            <div class="mobile-brand visual-brand"><span class="visual-logo"><i data-lucide="heart-handshake"></i></span><span><strong>Sahabat<br>Sekolah</strong><small>Sekolah Aman,<br>Semua Nyaman</small></span></div>
            <div class="login-emblem"><i data-lucide="key-round"></i></div>
            <span class="eyebrow">PEMULIHAN KATA SANDI</span>
            <h1>Lupa Kata Sandi?</h1>
            <p class="login-lead">Masukkan alamat email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi melalui email.</p>

            @if (session('status'))
                <div style="display:flex; gap:9px; align-items:flex-start; color:#187349; background:#e8f9f0; border:1px solid #c2eed5; border-radius:8px; padding:12px 14px; font-size:13px; line-height:1.5; margin-bottom:18px; text-align:left;">
                    <i data-lucide="circle-check-big" style="width:18px; flex:none; color:#187349; margin-top:1px;"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="login-error"><i data-lucide="circle-alert"></i>{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-error"><i data-lucide="circle-alert"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="reference-login-form">
                @csrf
                <label>
                    <i data-lucide="mail"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email terdaftar..." required autofocus>
                </label>
                <button class="reference-submit" type="submit" style="margin-top: 6px;">Kirim Tautan Reset Password</button>
            </form>

            <div class="login-divider"><span>atau</span></div>

            <a class="public-report-button" href="{{ route('login') }}">
                <i data-lucide="arrow-left"></i> Kembali ke Halaman Masuk
            </a>

            <div class="login-footer">
                <strong>Sahabat Sekolah</strong>
                <span>Dinas Pendidikan Kota Bukittinggi</span>
            </div>
        </div>
    </section>
</div>
<script>
    lucide.createIcons();
</script>
</body>
</html>
