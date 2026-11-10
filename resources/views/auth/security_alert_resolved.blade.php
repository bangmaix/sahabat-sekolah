<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Diamankan | Sahabat Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f5f8fc] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl border border-[#e1eaf2] shadow-[0_8px_30px_rgba(18,56,106,0.06)] p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-5 shadow-sm">
            <i data-lucide="shield-alert" class="w-8 h-8"></i>
        </div>

        <span class="inline-block px-3 py-1 bg-red-50 text-red-700 text-[12px] font-extrabold uppercase tracking-wider rounded-full border border-red-200 mb-3">
            Tindakan Keamanan Berhasil
        </span>

        <h1 class="text-xl font-extrabold text-[#11366a] mb-3">
            Akun Anda Telah Dinonaktifkan Sementara
        </h1>

        <p class="text-[15px] text-[#526b84] leading-relaxed mb-6">
            Terima kasih atas konfirmasi Anda. Sistem telah memverifikasi laporan bahwa perubahan kata sandi untuk akun <strong class="text-[#11366a] font-bold">{{ $email }}</strong> bukan dilakukan oleh Anda.
        </p>

        <div class="bg-[#f8fafc] border border-[#e9edf2] rounded-xl p-5 text-left mb-6 space-y-3">
            <div class="flex items-start gap-3">
                <i data-lucide="lock" class="w-5 h-5 text-red-500 shrink-0 mt-0.5"></i>
                <div>
                    <strong class="text-[14px] text-[#1e3a58] block">Akses Akun Terkunci</strong>
                    <p class="text-[13px] text-[#6b7f94] mt-0.5">Seluruh sesi aktif diputus dan upaya masuk dengan kata sandi baru akan ditolak otomatis oleh sistem.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 border-t border-[#edf2f7] pt-3">
                <i data-lucide="bell-ring" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                <div>
                    <strong class="text-[14px] text-[#1e3a58] block">Laporan Dikirim ke Administrator</strong>
                    <p class="text-[13px] text-[#6b7f94] mt-0.5">Administrator Sahabat Sekolah telah menerima pemberitahuan darurat untuk mengaudit aktivitas dan membantu pemulihan akun Anda.</p>
                </div>
            </div>
        </div>

        <p class="text-[13px] text-[#8295a8] mb-6">
            Silakan hubungi pihak sekolah atau Administrator platform untuk proses verifikasi dan penerbitan kembali akses akun Anda secara aman.
        </p>

        <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 bg-[#1878e3] text-white text-[14px] font-bold px-6 py-3 rounded-xl shadow-md hover:bg-[#1262c8] transition-colors">
            <i data-lucide="home" class="w-4 h-4"></i> Kembali ke Halaman Utama
        </a>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
