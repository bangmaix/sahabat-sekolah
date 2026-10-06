<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sahabat Sekolah' }}</title>
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
                                        <span class="notif-unread-badge">{{ $headerUnreadCount }} belum dibaca</span>
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
                            </div>
                        </div>
                    @endif
                    <div class="avatar">
                        {{ strtoupper(substr(session('user_name', 'BK'), 0, 2)) }}
                    </div>
                    <div>
                        <strong>{{ session('user_name', 'Bu Ratna Sari') }}</strong>
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
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit" title="Keluar">
                            <i data-lucide="log-out"></i>
                        </button>
                    </form>
                </div>
            </header>

            @if (session('success'))
                <div class="success-banner"><i data-lucide="circle-check"></i>{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="success-banner error-banner"><i data-lucide="circle-alert"></i>{{ $errors->first() }}</div>
            @endif

            @yield('content')

            <footer>
                <span>© 2026 Sahabat Sekolah</span>
                <span>“Dengarkan, Lindungi, Dampingi”</span>
                <span>Sistem Proteksi Siswa Terpadu</span>
            </footer>
        </main>
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
        });
    </script>
</body>

</html>
