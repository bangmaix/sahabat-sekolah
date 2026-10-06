<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Sahabat Sekolah</title>
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
        <div class="visual-copy"><span class="visual-kicker">KOTA BUKITTINGGI</span><h2>Bersama<br>Mencegah<br>Perundungan</h2><div class="yellow-stroke"></div><p>“Dari Ranah Minang,<br>untuk sekolah yang<br>lebih baik.”</p></div>
        <div class="visual-children"><span class="child child-one"></span><span class="child child-two"></span><span class="child child-three"></span><span class="child child-four"></span></div>
        <div class="visual-wave"></div>
        <div class="visual-values"><span><i data-lucide="shield-check"></i>Aman</span><span><i data-lucide="users"></i>Peduli</span><span><i data-lucide="leaf"></i>Saling Mendukung</span><span><i data-lucide="graduation-cap"></i>Sekolah Lebih Baik</span></div>
    </section>
    <section class="login-panel">
        <div class="login-panel-inner">
            <div class="mobile-brand visual-brand"><span class="visual-logo"><i data-lucide="heart-handshake"></i></span><span><strong>Sahabat<br>Sekolah</strong><small>Sekolah Aman,<br>Semua Nyaman</small></span></div>
            <div class="login-emblem"><i data-lucide="heart-handshake"></i></div>
            <span class="eyebrow">RUANG KERJA TERLINDUNGI</span>
            <h1>Selamat Datang<br><b>Guru BK</b></h1>
            <p class="login-lead">Masuk untuk mengakses dashboard<br>dan mengelola penanganan perundungan di sekolah.</p>
            @if (session('error'))<div class="login-error"><i data-lucide="circle-alert"></i>{{ session('error') }}</div>@endif
            <form method="POST" action="{{ route('login.store') }}" class="reference-login-form">
                @csrf
                <label><i data-lucide="user-round"></i><input type="email" name="email" value="{{ old('email') }}" placeholder="NIS / Email / Username" required></label>
                <label><i data-lucide="lock-keyhole"></i><input type="password" id="password" name="password" placeholder="Password" required><span class="field-action" id="togglePassword" style="cursor: pointer; z-index: 10;"><i data-lucide="eye" id="eyeIcon"></i><i data-lucide="eye-off" id="eyeOffIcon" style="display: none;"></i></span></label>
                <div class="login-options"><label class="remember"><input type="checkbox" name="remember"> <span>Ingat saya</span></label><a href="{{ route('reports.create') }}">Lupa password?</a></div>
                <button class="reference-submit" type="submit">Masuk</button>
            </form>
            <div class="login-divider"><span>atau</span></div>
            <a class="public-report-button" href="{{ route('reports.create') }}"><i data-lucide="shield-plus"></i> Buat laporan aman</a>
            <div class="login-footer"><strong>Sahabat Sekolah</strong><span>Dinas Pendidikan Kota Bukittinggi</span></div>
        </div>
    </section>
</div>
<script>
    lucide.createIcons();
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function (e) {
            e.preventDefault();
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            if (type === 'text') {
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>
