@extends('layouts.app', [
    'title' => 'Notifikasi | Sahabat Sekolah',
    'activeNav' => 'notifications',
    'eyebrow' => 'NOTIFICATION CENTER',
    'pageTitle' => 'Notifikasi Sistem',
    'pageSubtitle' => 'Peristiwa penting dan pembaruan kasus yang membutuhkan perhatian Anda.'
])

@section('content')
@php
    $unreadCount = $notifications->whereNull('read_at')->count();
    $totalCount = $notifications->count();
@endphp

<form id="bulkReadForm" method="POST" action="{{ route('notifications.read-selected') }}" style="display: none;">
    @csrf
</form>

<div class="notification-heading-bar">
    <div class="notification-heading-left">
        <span class="notification-count" id="unreadBadgeCount">{{ $unreadCount }} belum dibaca</span>
        @if ($totalCount > 0)
            <label class="notification-select-all-label" title="Pilih semua notifikasi">
                <input type="checkbox" id="selectAllCheckbox" class="notif-checkbox-custom">
                <span>Pilih Semua</span>
            </label>
        @endif
    </div>

    <div class="notification-heading-actions">
        @if ($totalCount > 0)
            <button type="submit" form="bulkReadForm" id="markSelectedBtn" class="notif-action-btn notif-btn-primary" disabled>
                <i data-lucide="check-check"></i>
                <span>Tandai Baca Terpilih (<span id="selectedCount">0</span>)</span>
            </button>
        @endif

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}" class="inline-form" onsubmit="return confirm('Apakah Anda yakin ingin menandai semua notifikasi sebagai telah dibaca?')">
                @csrf
                <button type="submit" class="notif-action-btn notif-btn-outline" title="Tandai seluruh notifikasi yang belum dibaca">
                    <i data-lucide="mail-check"></i>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </form>
        @endif
    </div>
</div>

<section class="notification-list">
    @forelse ($notifications as $notification)
        <article class="notification-item {{ $notification->read_at ? 'read' : 'unread' }}" id="notif-item-{{ $notification->id }}">
            <div class="notification-selection">
                <input type="checkbox" form="bulkReadForm" name="notification_ids[]" value="{{ $notification->id }}" class="notif-select-checkbox notif-checkbox-custom" aria-label="Pilih notifikasi {{ $notification->title }}" data-read="{{ $notification->read_at ? '1' : '0' }}">
            </div>
            <div class="notification-icon {{ strtolower($notification->priority) }}">
                <i data-lucide="{{ $notification->priority === 'URGENT' ? 'triangle-alert' : ($notification->type === 'NEW_REPORT' ? 'inbox' : 'bell') }}"></i>
            </div>
            <div class="notification-copy">
                <div>
                    <strong>{{ $notification->title }}</strong>
                    <small>{{ $notification->created_at ? \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() : '-' }}</small>
                </div>
                <p>{{ $notification->message }}</p>
                @if ($notification->case_number)
                    <a href="{{ route('cases.show', $notification->case_number) }}">
                        Buka kasus <i data-lucide="arrow-up-right"></i>
                    </a>
                @endif
            </div>
            @if (!$notification->read_at)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline-form">
                    @csrf
                    <button class="read-button" type="submit" title="Tandai notifikasi ini sudah dibaca">Tandai dibaca</button>
                </form>
            @endif
        </article>
    @empty
        <div class="empty-notifications">
            <i data-lucide="bell-off"></i>
            <strong>Belum ada notifikasi</strong>
            <p>Notifikasi baru akan muncul saat ada laporan atau aktivitas kasus.</p>
        </div>
    @endforelse
</section>

<style>
    .notification-heading-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .notification-heading-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .notification-heading-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .notification-select-all-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font: 600 11px 'DM Sans', sans-serif;
        color: #486581;
        cursor: pointer;
        user-select: none;
        background: #ffffff;
        border: 1px solid #dceaf6;
        border-radius: 20px;
        padding: 5px 12px;
        transition: all .15s ease;
    }

    .notification-select-all-label:hover {
        background: #f0f7ff;
        border-color: #bcd9f5;
        color: #1877e3;
    }

    .notif-checkbox-custom {
        width: 17px;
        height: 17px;
        accent-color: #1877e3;
        cursor: pointer;
        border-radius: 4px;
        margin: 0;
    }

    .notification-selection {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 2px;
        flex: none;
    }

    .notif-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 8px;
        font: 600 11px 'DM Sans', sans-serif;
        cursor: pointer;
        transition: all .2s ease;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .notif-action-btn svg {
        width: 14px;
        height: 14px;
    }

    .notif-btn-primary {
        background: linear-gradient(135deg, #1877e3 0%, #115eb7 100%);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(24, 119, 227, .25);
    }

    .notif-btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, #2284f3 0%, #156ccf 100%);
        box-shadow: 0 4px 12px rgba(24, 119, 227, .35);
        transform: translateY(-1px);
    }

    .notif-btn-primary:disabled {
        background: #e2eaf2;
        color: #9ab0c4;
        cursor: not-allowed;
        box-shadow: none;
        border-color: #d8e4f0;
    }

    .notif-btn-outline {
        background: #ffffff;
        border-color: #cde0f1;
        color: #276299;
    }

    .notif-btn-outline:hover {
        background: #f0f7ff;
        border-color: #9dc6ee;
        color: #1877e3;
        transform: translateY(-1px);
    }

    .inline-form {
        display: inline;
        margin: 0;
        padding: 0;
    }

    .notification-item {
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .notification-item.item-selected {
        background: #f4f9ff;
        border-color: #9bc5ee;
        box-shadow: 0 3px 12px rgba(24, 119, 227, .08);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const markSelectedBtn = document.getElementById('markSelectedBtn');
        const selectedCountSpan = document.getElementById('selectedCount');
        const notifCheckboxes = document.querySelectorAll('.notif-select-checkbox');

        function updateSelectionState() {
            const checkedCheckboxes = document.querySelectorAll('.notif-select-checkbox:checked');
            const count = checkedCheckboxes.length;

            if (selectedCountSpan) {
                selectedCountSpan.textContent = count;
            }

            if (markSelectedBtn) {
                markSelectedBtn.disabled = count === 0;
            }

            if (selectAllCheckbox && notifCheckboxes.length > 0) {
                selectAllCheckbox.checked = count === notifCheckboxes.length;
                selectAllCheckbox.indeterminate = count > 0 && count < notifCheckboxes.length;
            }

            notifCheckboxes.forEach(cb => {
                const article = cb.closest('.notification-item');
                if (article) {
                    if (cb.checked) {
                        article.classList.add('item-selected');
                    } else {
                        article.classList.remove('item-selected');
                    }
                }
            });
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                const isChecked = selectAllCheckbox.checked;
                notifCheckboxes.forEach(cb => {
                    cb.checked = isChecked;
                });
                updateSelectionState();
            });
        }

        notifCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectionState);
        });

        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>
@endsection

