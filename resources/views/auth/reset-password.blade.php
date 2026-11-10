<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi | Sahabat Sekolah</title>
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
        <div class="visual-copy"><span class="visual-kicker">KOTA BUKITTINGGI</span><h2>Buat Kata<br>Sandi Baru</h2><div class="yellow-stroke"></div><p>“Pastikan kata sandi baru<br>kuat dan tidak mudah<br>ditebak pihak lain.”</p></div>
        <div class="visual-children"><span class="child child-one"></span><span class="child child-two"></span><span class="child child-three"></span><span class="child child-four"></span></div>
        <div class="visual-wave"></div>
        <div class="visual-values"><span><i data-lucide="shield-check"></i>Aman</span><span><i data-lucide="users"></i>Peduli</span><span><i data-lucide="leaf"></i>Saling Mendukung</span><span><i data-lucide="graduation-cap"></i>Sekolah Lebih Baik</span></div>
    </section>
    <section class="login-panel">
        <div class="login-panel-inner">
            <div class="mobile-brand visual-brand"><span class="visual-logo"><i data-lucide="heart-handshake"></i></span><span><strong>Sahabat<br>Sekolah</strong><small>Sekolah Aman,<br>Semua Nyaman</small></span></div>
            <div class="login-emblem"><i data-lucide="lock-keyhole"></i></div>
            <span class="eyebrow">PEMBARUAN KREDENSIAL</span>
            <h1>Atur Kata Sandi Baru</h1>
            <p class="login-lead">Silakan buat kata sandi baru untuk akun Anda (minimal 8 karakter).</p>

            @if (session('error'))
                <div class="login-error"><i data-lucide="circle-alert"></i>{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-error"><i data-lucide="circle-alert"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="reference-login-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label>
                    <i data-lucide="mail"></i>
                    <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Alamat Email" required>
                </label>

                <label>
                    <i data-lucide="lock"></i>
                    <input type="password" id="password" name="password" placeholder="Kata sandi baru (min. 8 karakter)" required>
                    <span class="field-action" id="togglePassword" style="cursor: pointer; z-index: 10;">
                        <i data-lucide="eye" id="eyeIcon"></i>
                        <i data-lucide="eye-off" id="eyeOffIcon" style="display: none;"></i>
                    </span>
                </label>

                <label>
                    <i data-lucide="shield-check"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru" required>
                    <span class="field-action" id="togglePasswordConfirm" style="cursor: pointer; z-index: 10;">
                        <i data-lucide="eye" id="eyeIconConfirm"></i>
                        <i data-lucide="eye-off" id="eyeOffIconConfirm" style="display: none;"></i>
                    </span>
                </label>

                <button class="reference-submit" type="submit" style="margin-top: 8px;">Simpan Kata Sandi Baru</button>
            </form>

            <div class="login-divider"><span>atau</span></div>

            <a class="public-report-button" href="{{ route('login') }}">
                <i data-lucide="arrow-left"></i> Batalkan & Kembali Masuk
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

    function setupToggle(toggleId, inputId, eyeId, eyeOffId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);
        const eyeOff = document.getElementById(eyeOffId);
        if (toggle && input) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                if (type === 'text') {
                    eye.style.display = 'none';
                    eyeOff.style.display = 'block';
                } else {
                    eye.style.display = 'block';
                    eyeOff.style.display = 'none';
                }
            });
        }
    }

    setupToggle('togglePassword', 'password', 'eyeIcon', 'eyeOffIcon');
    setupToggle('togglePasswordConfirm', 'password_confirmation', 'eyeIconConfirm', 'eyeOffIconConfirm');
</script>
</body>
</html>
