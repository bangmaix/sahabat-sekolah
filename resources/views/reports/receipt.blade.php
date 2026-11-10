<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Laporan | Sahabat Sekolah</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .token-pill {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased flex flex-col justify-between">

    <!-- Tombol Keluar Cepat -->
    <a href="https://www.google.com" id="panic-exit-btn" class="fixed top-3 right-3 md:top-5 md:right-5 z-50 flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs md:text-sm px-3.5 py-2 rounded-full shadow-lg transition-all duration-200 cursor-pointer" title="Klik tombol ini untuk segera menutup halaman jika ada orang mendekat">
        <i data-lucide="shield-alert" class="w-4 h-4"></i>
        <span>Keluar Cepat</span>
    </a>

    <div class="max-w-2xl mx-auto w-full px-4 py-8 md:py-12 flex-1 flex flex-col justify-center">

        @if (!empty($isCrisis))
            <!-- Layar Bantuan Darurat Krisis -->
            <div class="mb-6 p-4 md:p-5 bg-rose-50 border-2 border-rose-300 rounded-2xl shadow-sm text-rose-900 animate-pulse">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-rose-100 rounded-xl text-rose-600 flex-none">
                        <i data-lucide="phone-call" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-rose-900 mb-1">Butuh Bantuan Segera? Kamu Tidak Sendirian.</h2>
                        <p class="text-xs md:text-sm text-rose-800 leading-relaxed mb-3">
                            Laporanmu telah masuk ke prioritas darurat <strong>KRITIS</strong>. Jika saat ini kamu merasa dalam bahaya fisik atau membutuhkan teman bicara darurat, jangan ragu untuk menghubungi layanan bantuan resmi di bawah ini:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <a href="tel:112" class="flex items-center justify-between p-2.5 bg-white rounded-lg border border-rose-200 font-semibold text-rose-700 hover:bg-rose-100 transition-colors">
                                <span>Panggilan Darurat Kota</span>
                                <span class="bg-rose-600 text-white px-2 py-0.5 rounded text-xs font-bold">112</span>
                            </a>
                            <a href="tel:129" class="flex items-center justify-between p-2.5 bg-white rounded-lg border border-rose-200 font-semibold text-rose-700 hover:bg-rose-100 transition-colors">
                                <span>Hotline Sahabat Anak (SAPA)</span>
                                <span class="bg-rose-600 text-white px-2 py-0.5 rounded text-xs font-bold">129</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <main class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-100 relative overflow-hidden">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>

            <div class="text-center mb-6">
                <span class="inline-block text-xs font-bold tracking-wider uppercase text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full mb-2">Laporan Berhasil Terkirim</span>
                <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Kartu Bukti Lapor Kamu</h1>
                <p class="text-xs md:text-sm text-slate-500 max-w-md mx-auto mt-1">
                    Laporanmu telah diterima secara aman oleh Guru BK. Simpan kartu ini atau ambil <em>screenshot</em> agar kamu bisa mengecek balasan.
                </p>
            </div>

            <!-- Kartu Bukti Lapor -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 mb-6 relative">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                    <div>
                        <span class="text-[13px] font-semibold text-slate-400 uppercase tracking-wider block">Nomor Laporan</span>
                        <strong class="text-sm md:text-base font-bold text-slate-800">{{ $reportNumber }}</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-[13px] font-semibold text-slate-400 uppercase tracking-wider block">Waktu Lapor</span>
                        <span class="text-xs font-medium text-slate-600">{{ now()->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>
                </div>

                @if (!empty($anonymousToken))
                <div class="bg-white border-2 border-dashed border-blue-200 rounded-xl p-4 text-center">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block mb-1">Kunci Rahasia Laporanmu (4 Kata)</span>
                    <div class="token-pill text-white py-3 px-4 rounded-xl font-mono text-sm md:text-lg font-bold my-2 shadow-inner select-all" id="anonymous-token">
                        {{ $anonymousToken }}
                    </div>
                    <div class="flex items-center justify-center gap-2 mt-3">
                        <button type="button" onclick="copyToken()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span id="copy-btn-text">Salin 4 Kata</span>
                        </button>
                        <span class="text-[13px] text-slate-400">|</span>
                        <span class="text-[13px] text-slate-500 font-medium">📸 Jangan lupa di-screenshot!</span>
                    </div>
                    <div id="copy-message" class="text-xs text-emerald-600 font-medium mt-2 hidden">4 Kata berhasil disalin ke clipboard!</div>
                </div>

                <p class="text-[13px] text-slate-400 text-center mt-3 leading-relaxed">
                    Catat atau simpan 4 kata rahasia di atas. Kamu dapat memakainya kapan saja untuk melihat status penanganan dan membalas pesan Guru BK tanpa pernah diketahui siapa dirimu.
                </p>
                @else
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider block mb-1">Nomor Registrasi Laporan</span>
                    <div class="text-slate-900 py-2 px-4 rounded-xl font-mono text-lg md:text-xl font-extrabold my-1 select-all">
                        {{ $reportNumber }}
                    </div>
                    <p class="text-xs text-slate-600 mt-1">
                        Gunakan nomor ini kapan saja untuk memantau posisi dan perkembangan penanganan kasusmu oleh Guru BK.
                    </p>
                </div>
                @endif
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('reports.track', ['q' => $anonymousToken ?? $reportNumber]) }}" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-3 rounded-xl shadow-md shadow-blue-500/20 transition-all text-center">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                    <span>Lacak Posisi Laporan</span>
                </a>
                @if (!empty($anonymousToken))
                <a href="{{ route('anonymous.channel', ['token' => $anonymousToken]) }}" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-5 py-3 rounded-xl shadow-md shadow-emerald-500/20 transition-all text-center">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Buka Ruang Obrolan Rahasia</span>
                </a>
                @endif
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm px-5 py-3 rounded-xl transition-colors text-center">
                    <span>Beranda</span>
                </a>
            </div>
        </main>

        <p class="text-center text-xs text-slate-400 mt-6">
            Sahabat Sekolah &copy; {{ date('Y') }} &middot; Platform Perlindungan & Penanganan Perundungan Ramah Anak Kota Bukittinggi.
        </p>
    </div>

    <script>
        lucide.createIcons();

        function copyToken() {
            const tokenText = document.getElementById('anonymous-token').textContent.trim();
            navigator.clipboard.writeText(tokenText).then(() => {
                const msg = document.getElementById('copy-message');
                const btnText = document.getElementById('copy-btn-text');
                msg.classList.remove('hidden');
                btnText.textContent = 'Tersalin!';
                setTimeout(() => {
                    msg.classList.add('hidden');
                    btnText.textContent = 'Salin 4 Kata';
                }, 3000);
            });
        }

        const panicBtn = document.getElementById('panic-exit-btn');
        if (panicBtn) {
            panicBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.replace('https://www.google.com');
            });
        }
    </script>
</body>
</html>
