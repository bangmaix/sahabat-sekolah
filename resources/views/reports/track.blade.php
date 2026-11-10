<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Status & Posisi Laporan | Sahabat Sekolah</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .stepper-line {
            height: 4px;
            flex: 1;
            transition: all 0.3s ease;
        }
        .token-pill {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            letter-spacing: 0.5px;
        }
        .pulse-ring {
            animation: pulseRing 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulseRing {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.08); }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased flex flex-col justify-between selection:bg-blue-500 selection:text-white">

    <!-- Tombol Keluar Cepat (Panic Exit) -->
    <a href="https://www.google.com" id="panic-exit-btn" class="fixed top-3 right-3 md:top-5 md:right-5 z-50 flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs md:text-sm px-3.5 py-2 rounded-full shadow-lg transition-all duration-200 cursor-pointer" title="Klik tombol ini untuk segera menutup halaman jika ada orang mendekat">
        <i data-lucide="shield-alert" class="w-4 h-4"></i>
        <span>Keluar Cepat</span>
    </a>

    <!-- Header & Nav -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                    <i data-lucide="heart-handshake" class="w-5 h-5"></i>
                </div>
                <div>
                    <strong class="text-sm md:text-base font-extrabold text-slate-900 block leading-tight">Sahabat Sekolah</strong>
                    <small class="text-[12px] text-slate-500 font-medium block">Pemerintah Kota Bukittinggi</small>
                </div>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.create') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                    <i data-lucide="shield-plus" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Buat Laporan Baru</span>
                    <span class="sm:hidden">Lapor</span>
                </a>
                <a href="{{ route('landing') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 transition-colors">
                    Beranda
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto w-full px-4 py-8 md:py-12 flex-1">
        
        <!-- Judul & Search Box -->
        <div class="text-center max-w-2xl mx-auto mb-8">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold tracking-wider uppercase text-blue-600 bg-blue-50 px-3 py-1 rounded-full mb-3">
                <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                Pelacak Perkembangan Kasus
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                Posisi Penanganan Laporanmu
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed">
                Pantau setiap tahapan yang sedang dilakukan Guru BK secara transparan, aman, dan terjaga kerahasiaannya.
            </p>

            <!-- Form Pencarian Laporan -->
            <form action="{{ route('reports.track') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2 max-w-xl mx-auto">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $query }}" 
                        placeholder="Ketik 4 kata rahasia atau nomor laporan (SS-2026-...)" 
                        required
                        class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-xs md:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs"
                    >
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-6 py-3 rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    <span>Cek Status</span>
                </button>
            </form>

            <div class="flex flex-wrap items-center justify-center gap-3 mt-3 text-[13px] text-slate-500">
                <span class="flex items-center gap-1">
                    <i data-lucide="lock" class="w-3 h-3 text-emerald-600"></i>
                    <strong>Laporan Anonim:</strong> Gunakan 4 kata rahasia
                </span>
                <span class="text-slate-300 hidden sm:inline">&bull;</span>
                <span class="flex items-center gap-1">
                    <i data-lucide="file-text" class="w-3 h-3 text-blue-600"></i>
                    <strong>Laporan Lain:</strong> Gunakan Nomor Laporan (SS-2026-...)
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs md:text-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-none"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($searched && !$case)
            <!-- Tampilan Tidak Ditemukan -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-10 text-center shadow-xs">
                <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="file-question" class="w-7 h-7"></i>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-slate-900 mb-2">Laporan Tidak Ditemukan</h2>
                <p class="text-xs md:text-sm text-slate-600 max-w-md mx-auto leading-relaxed mb-6">
                    Kami tidak dapat menemukan laporan dengan kode <code class="bg-slate-100 px-2 py-0.5 rounded font-mono text-slate-800 font-bold">{{ $query }}</code>. Mohon periksa kembali ejaan 4 kata rahasia (gunakan tanda hubung <code>-</code>) atau periksa nomor laporan pada kartu bukti lapor Anda.
                </p>
                <div class="inline-flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('reports.track') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                        Reset Pencarian
                    </a>
                    <a href="{{ route('reports.create') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-colors">
                        Buat Laporan Baru
                    </a>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 max-w-md mx-auto text-left">
                    <span class="text-[13px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Bantuan Darurat Kota Bukittinggi:</span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="tel:112" class="p-2.5 bg-rose-50 text-rose-700 rounded-lg font-semibold flex items-center justify-between border border-rose-200">
                            <span>Darurat 112</span>
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                        </a>
                        <a href="tel:129" class="p-2.5 bg-rose-50 text-rose-700 rounded-lg font-semibold flex items-center justify-between border border-rose-200">
                            <span>SAPA Anak 129</span>
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>

        @elseif ($case && $report)
            <!-- Tampilan Data Laporan Ditemukan -->
            
            <!-- 1. Ringkasan Kasus Card -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-5 md:p-6 shadow-xs mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-5">
                    <div>
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nomor Laporan</span>
                            @if ($report->identity_mode === 'ANONYMOUS')
                                <span class="text-[13px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i> Mode Anonim Terlindungi
                                </span>
                            @elseif ($report->identity_mode === 'CONFIDENTIAL')
                                <span class="text-[13px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <i data-lucide="lock" class="w-3 h-3"></i> Kerahasiaan Terjaga (Guru BK)
                                </span>
                            @else
                                <span class="text-[13px] font-semibold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">
                                    Laporan Teridentifikasi
                                </span>
                            @endif
                        </div>
                        <h2 class="text-lg md:text-xl font-extrabold text-slate-900 font-mono tracking-tight flex items-center gap-2">
                            <span>{{ $report->report_number }}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $report->report_number }}'); alert('Nomor laporan tersalin!');" class="text-slate-400 hover:text-blue-600 transition-colors" title="Salin nomor laporan">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </h2>
                    </div>

                    <div class="flex flex-wrap md:flex-col md:items-end gap-1 text-xs">
                        <span class="text-slate-400">Waktu Masuk:</span>
                        <strong class="text-slate-700 font-semibold">
                            {{ \Carbon\Carbon::parse($report->submitted_at ?? $case->opened_at)->translatedFormat('d M Y, H:i') }} WIB
                        </strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block mb-0.5 font-medium text-[13px]">Sekolah / Madrasah</span>
                        <strong class="text-slate-800 text-sm font-bold block">{{ $schoolName }}</strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block mb-0.5 font-medium text-[13px]">Kategori Perundungan</span>
                        <strong class="text-slate-800 text-sm font-bold block">
                            {{ $report->category }}
                            @if (!empty($report->subcategory))
                                <span class="text-slate-500 font-normal">({{ $report->subcategory }})</span>
                            @endif
                        </strong>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 sm:col-span-2 md:col-span-1">
                        <span class="text-slate-400 block mb-0.5 font-medium text-[13px]">Penanggung Jawab</span>
                        <strong class="text-slate-800 text-sm font-bold block flex items-center gap-1.5">
                            <i data-lucide="user-check" class="w-4 h-4 text-blue-600"></i>
                            <span>{{ $case->assigned_to ?: 'Guru Bimbingan Konseling (BK)' }}</span>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- 2. Visual Stepper 5 Tahap Penanganan Kasus -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-8 shadow-xs mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base md:text-lg font-bold text-slate-900">Alur Progres Penanganan Kasus</h3>
                        <p class="text-xs text-slate-500">Standar 5 tahapan penanganan terstruktur Sahabat Sekolah</p>
                    </div>
                    <span class="text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-100">
                        Tahap {{ $activeStep }} dari 5
                    </span>
                </div>

                <!-- Horizontal Stepper Desktop / Tablet -->
                <div class="hidden md:block">
                    <div class="flex items-center justify-between relative mb-2">
                        <!-- Step 1: Laporan Diterima -->
                        <div class="flex flex-col items-center relative z-10 w-28 text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $activeStep >= 1 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-200 text-slate-500' }}">
                                @if ($activeStep > 1)
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <span class="text-xs font-bold mt-2 {{ $activeStep === 1 ? 'text-blue-600' : ($activeStep > 1 ? 'text-slate-800' : 'text-slate-400') }}">
                                Laporan Masuk
                            </span>
                            <span class="text-[12px] text-slate-400 mt-0.5">Diterima Guru BK</span>
                        </div>

                        <!-- Stepper Line 1-2 -->
                        <div class="stepper-line {{ $activeStep >= 2 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                        <!-- Step 2: Verifikasi -->
                        <div class="flex flex-col items-center relative z-10 w-28 text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $activeStep > 2 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : ($activeStep === 2 ? 'bg-blue-600 text-white pulse-ring shadow-lg shadow-blue-500/30' : 'bg-slate-200 text-slate-500') }}">
                                @if ($activeStep > 2)
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="search-check" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <span class="text-xs font-bold mt-2 {{ $activeStep === 2 ? 'text-blue-600' : ($activeStep > 2 ? 'text-slate-800' : 'text-slate-400') }}">
                                Verifikasi
                            </span>
                            <span class="text-[12px] text-slate-400 mt-0.5">Pemeriksaan bukti</span>
                        </div>

                        <!-- Stepper Line 2-3 -->
                        <div class="stepper-line {{ $activeStep >= 3 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                        <!-- Step 3: Dalam Penanganan -->
                        <div class="flex flex-col items-center relative z-10 w-28 text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $activeStep > 3 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : ($activeStep === 3 ? 'bg-blue-600 text-white pulse-ring shadow-lg shadow-blue-500/30' : 'bg-slate-200 text-slate-500') }}">
                                @if ($activeStep > 3)
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <span class="text-xs font-bold mt-2 {{ $activeStep === 3 ? 'text-blue-600' : ($activeStep > 3 ? 'text-slate-800' : 'text-slate-400') }}">
                                Penanganan
                            </span>
                            <span class="text-[12px] text-slate-400 mt-0.5">Konseling & aksi</span>
                        </div>

                        <!-- Stepper Line 3-4 -->
                        <div class="stepper-line {{ $activeStep >= 4 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                        <!-- Step 4: Penyelesaian -->
                        <div class="flex flex-col items-center relative z-10 w-28 text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $activeStep > 4 ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : ($activeStep === 4 ? 'bg-emerald-600 text-white pulse-ring shadow-lg shadow-emerald-500/30' : 'bg-slate-200 text-slate-500') }}">
                                @if ($activeStep > 4)
                                    <i data-lucide="check" class="w-5 h-5"></i>
                                @else
                                    <i data-lucide="award" class="w-5 h-5"></i>
                                @endif
                            </div>
                            <span class="text-xs font-bold mt-2 {{ $activeStep === 4 ? 'text-emerald-700' : ($activeStep > 4 ? 'text-slate-800' : 'text-slate-400') }}">
                                Selesai
                            </span>
                            <span class="text-[12px] text-slate-400 mt-0.5">Substansi tuntas</span>
                        </div>

                        <!-- Stepper Line 4-5 -->
                        <div class="stepper-line {{ $activeStep >= 5 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                        <!-- Step 5: Ditutup & Diarsipkan -->
                        <div class="flex flex-col items-center relative z-10 w-28 text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $activeStep === 5 ? 'bg-slate-800 text-white shadow-md' : 'bg-slate-200 text-slate-500' }}">
                                <i data-lucide="archive" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-bold mt-2 {{ $activeStep === 5 ? 'text-slate-900' : 'text-slate-400' }}">
                                Ditutup
                            </span>
                            <span class="text-[12px] text-slate-400 mt-0.5">Arsip aman</span>
                        </div>
                    </div>
                </div>

                <!-- Vertical Stepper Mobile -->
                <div class="md:hidden space-y-3">
                    <div class="flex items-start gap-3 p-2.5 rounded-xl {{ $activeStep === 1 ? 'bg-blue-50/70 border border-blue-200' : '' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-none {{ $activeStep > 1 ? 'bg-emerald-600 text-white' : ($activeStep === 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                            1
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-800 block">Laporan Masuk & Diterima</strong>
                            <span class="text-[13px] text-slate-500">Laporan tersimpan aman dan masuk ke Guru BK</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-2.5 rounded-xl {{ $activeStep === 2 ? 'bg-blue-50/70 border border-blue-200' : '' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-none {{ $activeStep > 2 ? 'bg-emerald-600 text-white' : ($activeStep === 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                            2
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-800 block">Verifikasi & Penilaian Risiko</strong>
                            <span class="text-[13px] text-slate-500">Guru BK meneliti kronologi dan bukti laporan</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-2.5 rounded-xl {{ $activeStep === 3 ? 'bg-blue-50/70 border border-blue-200' : '' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-none {{ $activeStep > 3 ? 'bg-emerald-600 text-white' : ($activeStep === 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                            3
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-800 block">Proses Penanganan Kasus</strong>
                            <span class="text-[13px] text-slate-500">Pendampingan, konseling, mediasi terarah</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-2.5 rounded-xl {{ $activeStep === 4 ? 'bg-emerald-50/70 border border-emerald-200' : '' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-none {{ $activeStep > 4 ? 'bg-emerald-600 text-white' : ($activeStep === 4 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                            4
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-800 block">Penyelesaian Kasus</strong>
                            <span class="text-[13px] text-slate-500">Penanganan substansi perundungan tuntas</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-2.5 rounded-xl {{ $activeStep === 5 ? 'bg-slate-100 border border-slate-200' : '' }}">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-none {{ $activeStep === 5 ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-500' }}">
                            5
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-800 block">Kasus Ditutup & Diarsipkan</strong>
                            <span class="text-[13px] text-slate-500">Dokumen penuntasan lengkap dan resmi diarsipkan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Kartu Detail Status Terkini (Human-Friendly Explanation) -->
            <div class="mb-6 rounded-3xl p-6 md:p-8 border shadow-sm
                @if ($case->status === 'PENDING_RESPONSE')
                    bg-gradient-to-br from-blue-50 to-indigo-50/50 border-blue-200 text-blue-950
                @elseif ($case->status === 'UNDER_VERIFICATION')
                    bg-gradient-to-br from-amber-50 to-orange-50/50 border-amber-200 text-amber-950
                @elseif ($case->status === 'WAITING_REPORTER_INFO')
                    bg-gradient-to-br from-purple-50 to-pink-50/50 border-purple-300 text-purple-950
                @elseif ($case->status === 'IN_HANDLING')
                    bg-gradient-to-br from-blue-50 to-sky-50/50 border-blue-200 text-blue-950
                @elseif ($case->status === 'RESOLVED')
                    bg-gradient-to-br from-emerald-50 to-teal-50/50 border-emerald-200 text-emerald-950
                @elseif ($case->status === 'CLOSED')
                    bg-gradient-to-br from-slate-50 to-gray-50 border-slate-200 text-slate-900
                @endif
            ">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-none shadow-xs
                        @if ($case->status === 'PENDING_RESPONSE')
                            bg-blue-600 text-white
                        @elseif ($case->status === 'UNDER_VERIFICATION')
                            bg-amber-600 text-white
                        @elseif ($case->status === 'WAITING_REPORTER_INFO')
                            bg-purple-600 text-white animate-bounce
                        @elseif ($case->status === 'IN_HANDLING')
                            bg-blue-600 text-white
                        @elseif ($case->status === 'RESOLVED')
                            bg-emerald-600 text-white
                        @elseif ($case->status === 'CLOSED')
                            bg-slate-700 text-white
                        @endif
                    ">
                        @if ($case->status === 'PENDING_RESPONSE')
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        @elseif ($case->status === 'UNDER_VERIFICATION')
                            <i data-lucide="search" class="w-6 h-6"></i>
                        @elseif ($case->status === 'WAITING_REPORTER_INFO')
                            <i data-lucide="message-circle-warning" class="w-6 h-6"></i>
                        @elseif ($case->status === 'IN_HANDLING')
                            <i data-lucide="shield" class="w-6 h-6"></i>
                        @elseif ($case->status === 'RESOLVED')
                            <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                        @elseif ($case->status === 'CLOSED')
                            <i data-lucide="archive" class="w-6 h-6"></i>
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="text-[13px] font-extrabold uppercase tracking-wider
                                @if ($case->status === 'PENDING_RESPONSE') text-blue-700
                                @elseif ($case->status === 'UNDER_VERIFICATION') text-amber-700
                                @elseif ($case->status === 'WAITING_REPORTER_INFO') text-purple-700
                                @elseif ($case->status === 'IN_HANDLING') text-blue-700
                                @elseif ($case->status === 'RESOLVED') text-emerald-700
                                @elseif ($case->status === 'CLOSED') text-slate-700
                                @endif
                            ">
                                Posisi Laporan Saat Ini
                            </span>
                        </div>

                        <h4 class="text-lg md:text-xl font-extrabold mb-2">
                            @if ($case->status === 'PENDING_RESPONSE')
                                Laporan Telah Masuk & Menunggu Respons Guru BK
                            @elseif ($case->status === 'UNDER_VERIFICATION')
                                Sedang Dalam Proses Verifikasi oleh Guru BK
                            @elseif ($case->status === 'WAITING_REPORTER_INFO')
                                Guru BK Menunggu Informasi Tambahan Darimu
                            @elseif ($case->status === 'IN_HANDLING')
                                Kasus Sedang Dalam Penanganan Aktif
                            @elseif ($case->status === 'RESOLVED')
                                Penanganan Kasus Telah Selesai
                            @elseif ($case->status === 'CLOSED')
                                Laporan Kasus Telah Selesai & Ditutup
                            @endif
                        </h4>

                        <p class="text-xs md:text-sm leading-relaxed opacity-90">
                            @if ($case->status === 'PENDING_RESPONSE')
                                Laporanmu telah aman tercatat di sistem dan notifikasi telah dikirimkan ke Guru BK {{ $schoolName }}. Standar waktu respons awal sistem memastikan laporan ditindaklanjuti maksimal 1x24 jam kerja.
                            @elseif ($case->status === 'UNDER_VERIFICATION')
                                Guru BK sedang meneliti kronologi, bukti pendukung, dan menilai tingkat risiko kasus untuk menyusun langkah pendampingan yang tepat dan aman bagi seluruh pihak.
                            @elseif ($case->status === 'WAITING_REPORTER_INFO')
                                Guru BK telah meninjau laporan dan memerlukan beberapa penjelasan tambahan untuk membantu penanganan. Silakan periksa ruang percakapan di bawah dan kirim balasanmu secara aman.
                            @elseif ($case->status === 'IN_HANDLING')
                                Guru BK sedang aktif menjalankan langkah-langkah konseling, pendampingan korban, koordinasi, maupun tindakan perlindungan sesuai prosedur ramah anak.
                            @elseif ($case->status === 'RESOLVED')
                                Seluruh rangkaian tindakan penanganan pokok atas kejadian ini telah dinyatakan selesai oleh Guru BK. Keadaan telah terkendali dan dipantau.
                            @elseif ($case->status === 'CLOSED')
                                Penanganan kasus telah tuntas sepenuhnya dengan kelengkapan dokumen berita acara penyelesaian yang telah diverifikasi dan diarsipkan.
                            @endif
                        </p>

                        @if ($case->status === 'WAITING_REPORTER_INFO')
                            <div class="mt-4 p-3 bg-white/80 border border-purple-200 rounded-xl text-purple-900 text-xs font-medium flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-purple-600 flex-none"></i>
                                <span>Kamu dapat membalas pesan Guru BK langsung pada kotak percakapan di bawah.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. Riwayat Milestone & Perjalanan Kasus -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-7 shadow-xs mb-6">
                <h3 class="text-sm md:text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                    <span>Riwayat Perjalanan Penanganan</span>
                </h3>

                <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <!-- Milestone Awal: Diterima -->
                    <div class="relative">
                        <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-xs"></div>
                        <div>
                            <strong class="text-xs font-bold text-slate-800 block">Laporan Diterima Sistem</strong>
                            <span class="text-[13px] text-slate-500">
                                {{ \Carbon\Carbon::parse($case->opened_at ?? $case->created_at)->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                            <p class="text-xs text-slate-600 mt-1">Laporan berhasil dicatat dengan nomor registrasi {{ $report->report_number }}.</p>
                        </div>
                    </div>

                    <!-- Milestone dari Case Status Histories -->
                    @forelse ($histories as $hist)
                        <div class="relative">
                            <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-xs"></div>
                            <div>
                                <strong class="text-xs font-bold text-slate-800 block">
                                    @if ($hist->new_status === 'UNDER_VERIFICATION')
                                        Tahap Verifikasi Dimulai
                                    @elseif ($hist->new_status === 'IN_HANDLING')
                                        Tahap Penanganan Aktif
                                    @elseif ($hist->new_status === 'WAITING_REPORTER_INFO')
                                        Menunggu Informasi Tambahan dari Pelapor
                                    @elseif ($hist->new_status === 'RESOLVED')
                                        Kasus Dinyatakan Selesai Ditangani
                                    @elseif ($hist->new_status === 'CLOSED')
                                        Kasus Resmi Ditutup & Diarsipkan
                                    @else
                                        Status: {{ str_replace('_', ' ', $hist->new_status) }}
                                    @endif
                                </strong>
                                <span class="text-[13px] text-slate-500">
                                    {{ \Carbon\Carbon::parse($hist->created_at)->translatedFormat('d F Y, H:i') }} WIB
                                </span>
                                @if (!empty($hist->reason))
                                    <p class="text-xs text-slate-600 mt-1 italic">"{{ $hist->reason }}"</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <!-- Jika belum ada transisi -->
                    @endforelse
                </div>
            </div>

            <!-- 5. Ruang Obrolan Anonim dengan Guru BK (Jika pemilik token anonim) -->
            @if ($isAnonymousOwner)
                <div class="bg-white border border-blue-200 rounded-3xl p-6 md:p-8 shadow-md shadow-blue-500/5 mb-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center flex-none">
                                <i data-lucide="messages-square" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Ruang Obrolan Rahasia dengan Guru BK</h3>
                                <p class="text-[13px] text-slate-500">Kirim dan terima klarifikasi tambahan tanpa membuka identitasmu</p>
                            </div>
                        </div>
                        <span class="text-[13px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 hidden sm:inline-flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Identitas Terjaga Penuh
                        </span>
                    </div>

                    <!-- Daftar Pesan -->
                    <div class="space-y-3 max-h-80 overflow-y-auto p-1 mb-5" id="chat-messages-container">
                        @forelse ($messages as $msg)
                            <div class="flex flex-col {{ $msg->sender_type === 'REPORTER' ? 'items-end' : 'items-start' }}">
                                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl px-4 py-2.5 text-xs md:text-sm {{ $msg->sender_type === 'REPORTER' ? 'bg-blue-600 text-white rounded-br-xs shadow-xs' : 'bg-slate-100 text-slate-800 rounded-bl-xs border border-slate-200/70' }}">
                                    <div class="flex items-center gap-2 mb-1 text-[12px] {{ $msg->sender_type === 'REPORTER' ? 'text-blue-100' : 'text-slate-400 font-semibold' }}">
                                        <span>{{ $msg->sender_type === 'REPORTER' ? 'Kamu (Pelapor Anonim)' : 'Guru BK Sekolah' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ \Carbon\Carbon::parse($msg->created_at)->translatedFormat('d M, H:i') }}</span>
                                    </div>
                                    <p class="leading-relaxed whitespace-pre-wrap">{{ $msg->message }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400">
                                <i data-lucide="message-square-dashed" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <p class="text-xs">Belum ada percakapan. Jika ada informasi atau tanggapan yang ingin kamu sampaikan kepada Guru BK, tulis pada kolom di bawah.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Form Balasan Pesan -->
                    <form action="{{ route('reports.track.message') }}" method="POST" class="border-t border-slate-100 pt-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="redirect_to" value="track">

                        <label for="reporter-message-input" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kirim Pesan Tambahan / Balasan:
                        </label>
                        <textarea 
                            id="reporter-message-input"
                            name="message" 
                            rows="3" 
                            minlength="5" 
                            maxlength="2000" 
                            required 
                            placeholder="Tuliskan keterangan tambahan atau jawaban atas pertanyaan Guru BK di sini..."
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white resize-y"
                        ></textarea>
                        
                        <div class="flex items-center justify-between mt-2.5">
                            <span class="text-[13px] text-slate-400">Pesan langsung masuk ke notifikasi Guru BK</span>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                <span>Kirim Pesan</span>
                            </button>
                        </div>
                    </form>
                </div>
            @elseif ($report->identity_mode === 'ANONYMOUS' && !$isAnonymousOwner)
                <!-- Catatan jika melacak laporan anonim menggunakan nomor laporan -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 mb-6 flex items-start gap-3">
                    <i data-lucide="lock" class="w-5 h-5 text-amber-600 flex-none mt-0.5"></i>
                    <div>
                        <strong class="font-bold block mb-0.5">Ingin Mengirim atau Membaca Pesan dari Guru BK?</strong>
                        <p class="leading-relaxed">
                            Laporan ini dibuat dalam mode anonim. Masukkan <strong>4 Kata Rahasia</strong> yang Anda peroleh saat pertama kali melapor pada kolom pencarian di bagian atas untuk membuka ruang obrolan dua arah dengan Guru BK.
                        </p>
                    </div>
                </div>
            @endif

        @else
            <!-- Tampilan Awal / Panduan Pelacakan (Belum Melakukan Pencarian) -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-10 shadow-xs text-center max-w-2xl mx-auto">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="help-circle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2">Bagaimana Cara Melacak Laporanmu?</h3>
                <p class="text-xs md:text-sm text-slate-500 leading-relaxed max-w-md mx-auto mb-8">
                    Kamu dapat mengetahui sejauh mana Guru BK menangani kasusmu kapan saja dengan mudah.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left mb-8">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs mb-2">1</div>
                        <h4 class="text-xs font-bold text-slate-800 mb-1">Gunakan Kunci 4 Kata Rahasia</h4>
                        <p class="text-[13px] text-slate-500 leading-relaxed">Khusus bagi pelapor anonim. Masukkan 4 kata seperti <code>kucing-biru-melompat-pagi</code> untuk membuka status dan fitur kirim pesan.</p>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs mb-2">2</div>
                        <h4 class="text-xs font-bold text-slate-800 mb-1">Gunakan Nomor Laporan</h4>
                        <p class="text-[13px] text-slate-500 leading-relaxed">Masukkan kode nomor laporan yang tertera pada kartu bukti lapor (contoh: <code>SS-2026-09-0001</code>).</p>
                    </div>
                </div>

                <div class="p-4 bg-blue-50/70 rounded-2xl border border-blue-100 text-xs text-blue-900 flex items-center justify-between">
                    <span>Belum memiliki laporan yang aktif?</span>
                    <a href="{{ route('reports.create') }}" class="font-bold text-blue-700 hover:text-blue-800 flex items-center gap-1">
                        <span>Buat Laporan Baru</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-6 text-center text-xs text-slate-400 mt-12">
        <div class="max-w-4xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span>&copy; {{ date('Y') }} Sahabat Sekolah &middot; Pemerintah Kota Bukittinggi</span>
            <span class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="hover:text-slate-600 transition-colors">Beranda</a>
                <a href="{{ route('reports.create') }}" class="hover:text-slate-600 transition-colors">Buat Laporan</a>
                <a href="{{ route('login') }}" class="hover:text-slate-600 transition-colors">Portal Staf</a>
            </span>
        </div>
    </footer>

    <script>
        lucide.createIcons();

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
