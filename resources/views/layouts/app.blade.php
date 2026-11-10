<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sahabat Sekolah' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="{{ asset('js/tw-select.js') }}" defer></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .change-password-button {
            border: 0;
            background: #edf5fb;
            color: #5c7b98;
            width: 31px;
            height: 31px;
            border-radius: 7px;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .change-password-button:hover {
            background: #e0efff;
            color: #1675e8;
        }

        .change-password-button svg {
            width: 15px;
        }
    </style>
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">✦</div>
                <div>
                    <strong>Sahabat<br>Sekolah</strong>
                    <small>
                        @if (session('user_role') === 'ADMIN')
                            Platform Admin
                        @elseif(session('user_role') === 'PRINCIPAL')
                            Monitoring Kepsek
                        @else
                            Dashboard Guru BK
                        @endif
                    </small>
                </div>
            </div>

            <nav>
                @if (session('user_role') === 'ADMIN')
                    <a class="nav-item {{ ($activeNav ?? '') === 'admin-dashboard' ? 'active' : '' }}"
                        href="{{ route('admin.index') }}">
                        <i data-lucide="layout-dashboard"></i> Overview Admin
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'admin-users' ? 'active' : '' }}"
                        href="{{ route('admin.users') }}">
                        <i data-lucide="users"></i> Pengguna
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'admin-schools' ? 'active' : '' }}"
                        href="{{ route('admin.schools') }}">
                        <i data-lucide="building-2"></i> Sekolah
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'achievements' ? 'active' : '' }}"
                        href="{{ route('achievements.index') }}">
                        <i data-lucide="trophy"></i> Prestasi Siswa
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'admin-master-data' ? 'active' : '' }}"
                        href="{{ route('admin.master-data') }}">
                        <i data-lucide="database"></i> Master Data
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'admin.sla' ? 'active' : '' }}"
                        href="{{ route('admin.sla-configurations') }}">
                        <i data-lucide="clock-4"></i> SLA & Risiko
                    </a>
                @elseif(session('user_role') === 'PRINCIPAL')
                    <a class="nav-item {{ ($activeNav ?? '') === 'principal' ? 'active' : '' }}"
                        href="{{ route('principal.index') }}">
                        <i data-lucide="shield-check"></i> Monitoring
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'inbox' ? 'active' : '' }}"
                        href="{{ route('reports.inbox') }}">
                        <i data-lucide="inbox"></i> Laporan Masuk
                        <b>{{ $reportsCount ?? \Illuminate\Support\Facades\DB::table('cases')->where('school_npsn', session('school_npsn'))->count() }}</b>
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'cases' ? 'active' : '' }}"
                        href="{{ route('reports.inbox', ['status' => 'IN_HANDLING']) }}">
                        <i data-lucide="briefcase-business"></i> Manajemen Kasus
                        @php
                            $activeCasesCount = \Illuminate\Support\Facades\DB::table('cases')
                                ->where('school_npsn', session('school_npsn'))
                                ->where('status', 'IN_HANDLING')
                                ->count();
                        @endphp
                        @if ($activeCasesCount > 0)
                            <b>{{ $activeCasesCount }}</b>
                        @endif
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'statistics' ? 'active' : '' }}"
                        href="{{ route('statistics.index') }}">
                        <i data-lucide="bar-chart-3"></i> Statistik
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'achievements' ? 'active' : '' }}"
                        href="{{ route('achievements.index') }}">
                        <i data-lucide="trophy"></i> Prestasi Siswa
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'notifications' ? 'active' : '' }}"
                        href="{{ route('notifications.index') }}">
                        <i data-lucide="bell"></i> Notifikasi
                        @php
                            $unreadNotifsCount = \Illuminate\Support\Facades\DB::table('notifications')
                                ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
                                ->where(function ($q) {
                                    $q->where('cases.school_npsn', session('school_npsn'))->orWhereNull(
                                        'notifications.case_number',
                                    );
                                })
                                ->whereNull('notifications.read_at')
                                ->count();
                        @endphp
                        @if ($unreadNotifsCount > 0)
                            <b>{{ $unreadNotifsCount }}</b>
                        @endif
                    </a>
                @else
                    <a class="nav-item {{ ($activeNav ?? '') === 'dashboard' ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i data-lucide="layout-dashboard"></i> Dashboard
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'inbox' ? 'active' : '' }}"
                        href="{{ route('reports.inbox') }}">
                        <i data-lucide="inbox"></i> Laporan Masuk
                        <b>{{ $reportsCount ?? \Illuminate\Support\Facades\DB::table('cases')->where('school_npsn', session('school_npsn'))->count() }}</b>
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'cases' ? 'active' : '' }}"
                        href="{{ route('reports.inbox', ['status' => 'IN_HANDLING']) }}">
                        <i data-lucide="briefcase-business"></i> Manajemen Kasus
                        @php
                            $activeCasesCount = \Illuminate\Support\Facades\DB::table('cases')
                                ->where('school_npsn', session('school_npsn'))
                                ->where('status', 'IN_HANDLING')
                                ->count();
                        @endphp
                        @if ($activeCasesCount > 0)
                            <b>{{ $activeCasesCount }}</b>
                        @endif
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'statistics' ? 'active' : '' }}"
                        href="{{ route('statistics.index') }}">
                        <i data-lucide="bar-chart-3"></i> Statistik
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'achievements' ? 'active' : '' }}"
                        href="{{ route('achievements.index') }}">
                        <i data-lucide="trophy"></i> Prestasi Siswa
                    </a>
                    <a class="nav-item {{ ($activeNav ?? '') === 'notifications' ? 'active' : '' }}"
                        href="{{ route('notifications.index') }}">
                        <i data-lucide="bell"></i> Notifikasi
                        @php
                            $unreadNotifsCount = \Illuminate\Support\Facades\DB::table('notifications')
                                ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
                                ->where(function ($q) {
                                    $q->where('cases.school_npsn', session('school_npsn'))->orWhereNull(
                                        'notifications.case_number',
                                    );
                                })
                                ->whereNull('notifications.read_at')
                                ->count();
                        @endphp
                        @if ($unreadNotifsCount > 0)
                            <b>{{ $unreadNotifsCount }}</b>
                        @endif
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <span class="online-dot"></span>
                <span>Semua sistem normal</span>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div>
                    <span
                        class="eyebrow">{{ $eyebrow ?? strtoupper(\Illuminate\Support\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y')) }}</span>
                    <h1>{{ $pageTitle ?? ($title ?? 'Dashboard') }}</h1>
                    @if (!empty($pageSubtitle))
                        <p>{{ $pageSubtitle }}</p>
                    @endif
                </div>

                <div class="profile">
                    @if (session('user_role') !== 'ADMIN')
                        @php
                            $headerNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                                ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
                                ->where(function ($q) {
                                    $q->where('cases.school_npsn', session('school_npsn'))->orWhereNull(
                                        'notifications.case_number',
                                    );
                                })
                                ->select('notifications.*')
                                ->orderByDesc('notifications.created_at')
                                ->limit(5)
                                ->get();
                            $headerUnreadCount = \Illuminate\Support\Facades\DB::table('notifications')
                                ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
                                ->where(function ($q) {
                                    $q->where('cases.school_npsn', session('school_npsn'))->orWhereNull(
                                        'notifications.case_number',
                                    );
                                })
                                ->whereNull('notifications.read_at')
                                ->count();
                        @endphp
                        <div class="notification-dropdown-wrapper">
                            <button type="button" class="icon-button notification-trigger" id="notifDropdownBtn"
                                title="Notifikasi" aria-expanded="false">
                                <i data-lucide="bell"></i>
                                @if ($headerUnreadCount > 0)
                                    <span class="notif-badge-dot"></span>
                                @endif
                            </button>
                            <div class="notification-dropdown-menu" id="notifDropdownMenu">
                                <div class="notif-dropdown-header">
                                    <strong>Notifikasi</strong>
                                    @if ($headerUnreadCount > 0)
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span class="notif-unread-badge">{{ $headerUnreadCount }} belum
                                                dibaca</span>
                                            <form method="POST" action="{{ route('notifications.read-all') }}"
                                                style="margin:0;">
                                                @csrf
                                                <button type="submit" class="notif-readall-btn"
                                                    title="Tandai semua notifikasi dibaca">
                                                    <i data-lucide="check-check"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <small class="notif-read-all">Semua dibaca</small>
                                    @endif
                                </div>
                                <div class="notif-dropdown-body">
                                    @forelse($headerNotifications as $notif)
                                        <a href="{{ route('notifications.read', $notif->id) }}"
                                            class="notif-dropdown-item {{ is_null($notif->read_at) ? 'unread' : '' }}">
                                            <div class="notif-dropdown-icon {{ strtolower($notif->priority) }}">
                                                @if ($notif->priority === 'URGENT')
                                                    <i data-lucide="triangle-alert"></i>
                                                @elseif($notif->priority === 'HIGH')
                                                    <i data-lucide="bell-ring"></i>
                                                @else
                                                    <i data-lucide="info"></i>
                                                @endif
                                            </div>
                                            <div class="notif-dropdown-content">
                                                <div class="notif-dropdown-top">
                                                    <strong>{{ $notif->title }}</strong>
                                                    <small>{{ \Illuminate\Support\Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                                                </div>
                                                <p>{{ \Illuminate\Support\Str::limit($notif->message, 65) }}</p>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="notif-dropdown-empty">
                                            <i data-lucide="bell-off"></i>
                                            <p>Tidak ada notifikasi</p>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="notif-dropdown-footer">
                                    <a href="{{ route('notifications.index') }}" class="notif-view-all">
                                        Lihat Semua Notifikasi <i data-lucide="arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="avatar">
                        {{ strtoupper(substr(session('user_name', 'BK'), 0, 2)) }}
                    </div>
                    <div>
                        <strong>{{ session('user_name', 'Guru BK') }}</strong>
                        <small>
                            @if (session('user_role') === 'ADMIN')
                                Administrator Platform
                            @elseif(session('user_role') === 'PRINCIPAL')
                                Kepala Sekolah · {{ session('nama_sekolah', session('school_npsn')) }}
                            @else
                                Guru BK · {{ session('nama_sekolah', session('school_npsn')) }}
                            @endif
                        </small>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button class="change-password-button" type="button" id="btn-change-password-global"
                            title="Ubah Kata Sandi Saya">
                            <i data-lucide="key-round"></i>
                        </button>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button class="logout-button" type="submit" title="Keluar">
                                <i data-lucide="log-out"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            @if (session('success'))
                <div class="success-banner"><i data-lucide="circle-check"></i>{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="success-banner error-banner"><i data-lucide="circle-alert"></i>{{ $errors->first() }}
                </div>
            @endif

            @yield('content')

            <footer>
                <span>© 2026 Sahabat Sekolah</span>
                <span>“Dengarkan, Lindungi, Dampingi”</span>
                <span>Sistem Proteksi Siswa Terpadu</span>
            </footer>
        </main>
    </div>

    {{-- ======================== MODAL: UBAH KATA SANDI USER ======================== --}}
    <div id="modal-change-password" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="modal-cp-title"
        style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:16px;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" id="modal-cp-backdrop"
            style="position:fixed; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(4px);"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-modal"
            style="position:relative; z-index:10000; max-width:440px; width:100%; background:#fff; border-radius:16px; padding:24px; box-shadow:0 20px 45px rgba(18,56,106,0.18);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
                <div>
                    <h2 id="modal-cp-title"
                        style="font:800 17px 'Plus Jakarta Sans', sans-serif; color:#12386a; margin:0;">Ubah Kata Sandi
                    </h2>
                    <p style="font-size:13px; color:#7e91a7; margin:4px 0 0;">Perbarui kata sandi akun Anda untuk
                        menjaga keamanan.</p>
                </div>
                <button type="button" id="modal-cp-close"
                    style="width:30px; height:30px; border-radius:50%; border:0; background:#f1f5f9; color:#64748b; display:grid; place-items:center; cursor:pointer;">
                    <i data-lucide="x" style="width:16px;"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('password.change') }}" id="form-change-password"
                style="display:grid; gap:12px;">
                @csrf
                <div>
                    <label
                        style="display:block; font-size:12px; font-weight:700; color:#496985; margin-bottom:5px;">Kata
                        Sandi Saat Ini <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="current_password" required
                        placeholder="Masukkan kata sandi saat ini"
                        style="width:100%; border:1px solid #dfe8f1; border-radius:8px; padding:9px 12px; font-size:13px; color:#1e3a58; background:#fbfdff; outline:none;">
                </div>
                <div>
                    <label
                        style="display:block; font-size:12px; font-weight:700; color:#496985; margin-bottom:5px;">Kata
                        Sandi Baru <span style="color:#ef4444;">*</span> <small
                            style="color:#8ba0b3; font-weight:400;">(Min. 8 karakter)</small></label>
                    <input type="password" name="password" required minlength="8"
                        placeholder="Minimal 8 karakter baru"
                        style="width:100%; border:1px solid #dfe8f1; border-radius:8px; padding:9px 12px; font-size:13px; color:#1e3a58; background:#fbfdff; outline:none;">
                </div>
                <div>
                    <label
                        style="display:block; font-size:12px; font-weight:700; color:#496985; margin-bottom:5px;">Konfirmasi
                        Kata Sandi Baru <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8"
                        placeholder="Ulangi kata sandi baru"
                        style="width:100%; border:1px solid #dfe8f1; border-radius:8px; padding:9px 12px; font-size:13px; color:#1e3a58; background:#fbfdff; outline:none;">
                </div>

                <div
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:12px; color:#64748b; line-height:1.5;">
                    🔒 <strong>Pemberitahuan:</strong> Notifikasi keamanan akan otomatis dikirimkan ke email Anda begitu
                    kata sandi berhasil diperbarui.
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
                    <button type="button" id="modal-cp-cancel"
                        style="border:1px solid #dfe8f1; background:#f8fafc; color:#5c7b98; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                        style="border:0; background:#1878e3; color:#ffffff; padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px; cursor:pointer; box-shadow:0 4px 12px rgba(24,120,227,0.25);">
                        <i data-lucide="check" style="width:14px;"></i> Simpan Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            const btn = document.getElementById('notifDropdownBtn');
            const menu = document.getElementById('notifDropdownMenu');
            if (btn && menu) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isOpen = menu.classList.contains('show');
                    menu.classList.toggle('show', !isOpen);
                    btn.setAttribute('aria-expanded', !isOpen);
                });

                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && !btn.contains(e.target)) {
                        menu.classList.remove('show');
                        btn.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        menu.classList.remove('show');
                        btn.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // ---- Change Password Modal ----
            const modalCp = document.getElementById('modal-change-password');
            const btnOpenCp = document.getElementById('btn-change-password-global');
            const btnCloseCp = document.getElementById('modal-cp-close');
            const btnCancelCp = document.getElementById('modal-cp-cancel');
            const backdropCp = document.getElementById('modal-cp-backdrop');

            function openCpModal() {
                if (!modalCp) return;
                modalCp.style.display = 'flex';
                modalCp.classList.remove('hidden');
                lucide.createIcons();
            }

            function closeCpModal() {
                if (!modalCp) return;
                modalCp.style.display = 'none';
                modalCp.classList.add('hidden');
            }

            if (btnOpenCp) btnOpenCp.addEventListener('click', openCpModal);
            if (btnCloseCp) btnCloseCp.addEventListener('click', closeCpModal);
            if (btnCancelCp) btnCancelCp.addEventListener('click', closeCpModal);
            if (backdropCp) backdropCp.addEventListener('click', closeCpModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeCpModal();
                }
            });
        });
    </script>
</body>

</html>
