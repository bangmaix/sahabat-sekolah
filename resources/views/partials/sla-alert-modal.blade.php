@if (!empty($slaAlertSummary) && $slaAlertSummary['total'] > 0)
    {{-- =============================================================== --}}
    {{-- POPUP MODAL: SLA RESPONSE TIME OVERDUE / NEAR OVERDUE ALERT       --}}
    {{-- =============================================================== --}}
    <div id="slaAlertModal" class="sla-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="slaModalTitle" style="display: none;">
        <div class="sla-modal-card">
            {{-- Modal Header --}}
            <div class="sla-modal-header">
                <div class="sla-header-content">
                    <div class="sla-icon-badge {{ $slaAlertSummary['overdue'] > 0 ? 'is-overdue' : 'is-warning' }}">
                        <i data-lucide="{{ $slaAlertSummary['overdue'] > 0 ? 'triangle-alert' : 'clock-alert' }}"></i>
                    </div>
                    <div>
                        <div class="sla-title-row">
                            <h2 id="slaModalTitle">Peringatan SLA: Laporan Butuh Respons Segera</h2>
                            <span class="sla-live-pill">
                                <span class="sla-pulse-dot"></span> Perlu Tindakan
                            </span>
                        </div>
                        <p class="sla-subtitle">
                            Terdapat <strong>{{ $slaAlertSummary['total'] }} laporan siswa</strong> yang belum direspon dan telah melewati atau mendekati batas waktu SLA respons awal.
                        </p>
                    </div>
                </div>
                <button type="button" class="sla-close-btn" onclick="closeSlaModal()" aria-label="Tutup popup" title="Tutup">
                    <i data-lucide="x"></i>
                </button>
            </div>

            {{-- Summary Chips & Filter Tabs --}}
            <div class="sla-tabs-bar">
                <div class="sla-tabs-group" role="tablist">
                    <button type="button" class="sla-tab-btn active" data-filter="ALL" onclick="filterSlaCases('ALL', this)">
                        Semua Laporan <span class="sla-tab-count">{{ $slaAlertSummary['total'] }}</span>
                    </button>
                    @if ($slaAlertSummary['overdue'] > 0)
                        <button type="button" class="sla-tab-btn tab-overdue" data-filter="OVERDUE" onclick="filterSlaCases('OVERDUE', this)">
                            <i data-lucide="alert-circle"></i> Overdue <span class="sla-tab-count red">{{ $slaAlertSummary['overdue'] }}</span>
                        </button>
                    @endif
                    @if ($slaAlertSummary['near_overdue'] > 0)
                        <button type="button" class="sla-tab-btn tab-warning" data-filter="WARNING" onclick="filterSlaCases('WARNING', this)">
                            <i data-lucide="clock"></i> Mendekati Batas (< 6 Jam) <span class="sla-tab-count amber">{{ $slaAlertSummary['near_overdue'] }}</span>
                        </button>
                    @endif
                </div>
                <div class="sla-school-info">
                    <i data-lucide="shield-check"></i> Layanan BK Sekolah
                </div>
            </div>

            {{-- Modal Body: List of Unresponded Cases --}}
            <div class="sla-modal-body custom-scrollbar" id="slaCasesList">
                @foreach ($slaAlertCases as $alertCase)
                    <div class="sla-case-card {{ $alertCase->is_overdue ? 'card-overdue' : 'card-warning' }}" data-status="{{ $alertCase->effective_sla_status }}">
                        <div class="sla-card-top">
                            <div class="sla-card-meta">
                                <span class="sla-case-number">#{{ $alertCase->case_number }}</span>
                                <span class="sla-category-chip">{{ $alertCase->category }}</span>
                                <span class="sla-risk-chip risk-{{ strtolower($alertCase->risk_level) }}">
                                    {{ $alertCase->risk_level }}
                                </span>
                                <span class="sla-unresponded-badge">
                                    <i data-lucide="mail-warning"></i> Belum Direspon
                                </span>
                            </div>
                            <div class="sla-status-badge {{ $alertCase->is_overdue ? 'badge-overdue' : 'badge-warning' }}">
                                <i data-lucide="{{ $alertCase->is_overdue ? 'alert-octagon' : 'clock' }}"></i>
                                <strong>{{ $alertCase->is_overdue ? 'SLA Terlewat' : 'Mendekati Batas' }}</strong>
                                <span class="sla-time-diff">{{ $alertCase->deadline_human }}</span>
                            </div>
                        </div>

                        <div class="sla-card-body">
                            <div class="sla-description-box">
                                <i data-lucide="quote" class="sla-quote-icon"></i>
                                <p>{{ Str::limit($alertCase->description, 150) }}</p>
                            </div>
                            <div class="sla-case-submeta">
                                <span>
                                    <i data-lucide="user"></i>
                                    Peran: <strong>{{ match ($alertCase->reporter_role) {'VICTIM' => 'Korban langsung', 'WITNESS' => 'Saksi kejadian', default => 'Pelapor'} }}</strong>
                                </span>
                                <span>
                                    <i data-lucide="calendar"></i>
                                    Diterima: <strong>{{ \Illuminate\Support\Carbon::parse($alertCase->opened_at)->format('d M Y, H:i') }}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="sla-card-bottom">
                            <div class="sla-deadline-info">
                                <span class="deadline-label">Batas Waktu Respons:</span>
                                <strong class="deadline-val {{ $alertCase->is_overdue ? 'text-red' : 'text-amber' }}">
                                    {{ $alertCase->formatted_deadline }}
                                </strong>
                            </div>
                            <a href="{{ route('cases.show', $alertCase->case_number, false) }}" class="sla-action-btn" onclick="event.stopPropagation(); window.location.href=this.href;">
                                <span>Beri Respons Sekarang</span>
                                <i data-lucide="arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Modal Footer --}}
            <div class="sla-modal-footer">
                <div class="sla-footer-tip">
                    <i data-lucide="info"></i>
                    <span>Respons awal yang cepat memberikan kepastian psikologis dan rasa aman bagi siswa yang melapor.</span>
                </div>
                <div class="sla-footer-actions">
                    <a href="{{ route('reports.inbox', ['status' => 'PENDING_RESPONSE', 'sla_status' => 'OVERDUE'], false) }}" class="sla-footer-link" onclick="event.stopPropagation(); window.location.href=this.href;">
                        Lihat Semua di Inbox <i data-lucide="external-link"></i>
                    </a>
                    <button type="button" class="sla-dismiss-btn" onclick="closeSlaModal()">
                        Tutup Sementara
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL STYLES --}}
    <style>
        .sla-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: slaFadeIn 0.25s ease-out;
        }

        .sla-modal-card {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 740px;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(226, 232, 240, 0.8);
            overflow: hidden;
            animation: slaSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        @keyframes slaFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slaSlideUp {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(14px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .sla-modal-header {
            padding: 22px 24px 18px;
            border-bottom: 1px solid #edf2f7;
            background: linear-gradient(180deg, #fff9f9 0%, #ffffff 100%);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .sla-header-content {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .sla-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .sla-icon-badge.is-overdue {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            border: 1px solid #fca5a5;
            animation: alertPulse 2s infinite ease-in-out;
        }

        .sla-icon-badge.is-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
            border: 1px solid #fcd34d;
        }

        .sla-icon-badge svg {
            width: 24px;
            height: 24px;
        }

        @keyframes alertPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.3); }
            50% { transform: scale(1.04); box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
        }

        .sla-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sla-title-row h2 {
            margin: 0;
            font: 800 19px/1.3 'Plus Jakarta Sans', sans-serif;
            color: #0f2d59;
            letter-spacing: -0.02em;
        }

        .sla-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 20px;
            padding: 3px 9px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .sla-pulse-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #dc2626;
            display: inline-block;
            animation: dotBlink 1.4s infinite ease-in-out;
        }

        @keyframes dotBlink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.8); }
        }

        .sla-subtitle {
            margin: 6px 0 0;
            font-size: 14px;
            line-height: 1.55;
            color: #556987;
        }

        .sla-subtitle strong {
            color: #193863;
        }

        .sla-close-btn {
            background: #f1f5f9;
            border: 0;
            color: #64748b;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .sla-close-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: rotate(90deg);
        }

        .sla-close-btn svg {
            width: 17px;
            height: 17px;
        }

        .sla-tabs-bar {
            padding: 10px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .sla-tabs-group {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .sla-tab-btn {
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 6px 12px;
            font: 600 11px 'DM Sans', sans-serif;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .sla-tab-btn:hover {
            background: #ffffff;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        .sla-tab-btn.active {
            background: #ffffff;
            color: #0f2d59;
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            font-weight: 700;
        }

        .sla-tab-btn svg {
            width: 13px;
            height: 13px;
        }

        .sla-tab-count {
            background: #e2e8f0;
            color: #334155;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .sla-tab-count.red {
            background: #fee2e2;
            color: #b91c1c;
        }

        .sla-tab-count.amber {
            background: #fef3c7;
            color: #b45309;
        }

        .sla-school-info {
            font-size: 13px;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .sla-school-info svg {
            width: 14px;
            height: 14px;
            color: #0ea5e9;
        }

        .sla-modal-body {
            padding: 20px 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-height: calc(88vh - 210px);
        }

        .sla-case-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            transition: all 0.2s ease;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .sla-case-card:hover {
            box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .sla-case-card.card-overdue {
            border-left: 4px solid #ef4444;
            background: linear-gradient(180deg, #ffffff 0%, #fffbfb 100%);
        }

        .sla-case-card.card-warning {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(180deg, #ffffff 0%, #fffdf8 100%);
        }

        .sla-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .sla-card-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sla-case-number {
            font: 800 13px 'Plus Jakarta Sans', sans-serif;
            color: #0f2d59;
            letter-spacing: -0.01em;
        }

        .sla-category-chip {
            background: #eef5fb;
            color: #2b5d8f;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .sla-risk-chip {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sla-risk-chip.risk-critical {
            background: #ffe4e6;
            color: #e11d48;
            border: 1px solid #fecdd3;
        }

        .sla-risk-chip.risk-high,
        .sla-risk-chip.risk-tinggi {
            background: #ffedd5;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .sla-risk-chip.risk-medium,
        .sla-risk-chip.risk-sedang {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .sla-risk-chip.risk-low,
        .sla-risk-chip.risk-rendah {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .sla-unresponded-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .sla-unresponded-badge svg {
            width: 12px;
            height: 12px;
        }

        .sla-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .sla-status-badge svg {
            width: 14px;
            height: 14px;
        }

        .sla-status-badge.badge-overdue {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .sla-status-badge.badge-warning {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        .sla-time-diff {
            font-weight: 700;
            margin-left: 2px;
        }

        .sla-card-body {
            margin-bottom: 14px;
        }

        .sla-description-box {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: #f8fafc;
            border-radius: 9px;
            padding: 10px 12px;
            border: 1px solid #edf2f7;
            margin-bottom: 9px;
        }

        .sla-quote-icon {
            width: 14px;
            height: 14px;
            color: #94a3b8;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .sla-description-box p {
            margin: 0;
            font-size: 14px;
            line-height: 1.55;
            color: #334155;
            font-style: italic;
        }

        .sla-case-submeta {
            display: flex;
            gap: 16px;
            color: #64748b;
            font-size: 13px;
            flex-wrap: wrap;
        }

        .sla-case-submeta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .sla-case-submeta svg {
            width: 13px;
            height: 13px;
            color: #94a3b8;
        }

        .sla-case-submeta strong {
            color: #1e293b;
        }

        .sla-card-bottom {
            padding-top: 12px;
            border-top: 1px dashed #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .sla-deadline-info {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .sla-deadline-info .deadline-label {
            color: #64748b;
        }

        .sla-deadline-info .deadline-val {
            font-weight: 700;
        }

        .sla-deadline-info .text-red {
            color: #dc2626;
        }

        .sla-deadline-info .text-amber {
            color: #d97706;
        }

        .sla-action-btn {
            background: linear-gradient(135deg, #1675e8 0%, #0f5ec2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font: 700 11px 'DM Sans', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(22, 117, 232, 0.25);
            transition: all 0.15s ease;
            cursor: pointer;
            position: relative;
            z-index: 10;
        }

        .sla-action-btn * {
            pointer-events: none;
        }

        .sla-action-btn:hover {
            background: linear-gradient(135deg, #1265cc 0%, #0c4fa4 100%);
            transform: translateX(2px);
            box-shadow: 0 6px 16px rgba(22, 117, 232, 0.35);
        }

        .sla-action-btn:active {
            transform: scale(0.98);
        }

        .sla-action-btn svg {
            width: 13px;
            height: 13px;
        }

        .sla-modal-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .sla-footer-tip {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: 13px;
            flex: 1;
            min-width: 240px;
        }

        .sla-footer-tip svg {
            width: 16px;
            height: 16px;
            color: #0284c7;
            flex-shrink: 0;
        }

        .sla-footer-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sla-footer-link {
            color: #1675e8;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 7px 10px;
            border-radius: 6px;
            transition: background 0.15s ease;
            cursor: pointer;
            position: relative;
            z-index: 10;
        }

        .sla-footer-link * {
            pointer-events: none;
        }

        .sla-footer-link:hover {
            background: #e0f2fe;
        }

        .sla-footer-link svg {
            width: 13px;
            height: 13px;
        }

        .sla-dismiss-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 7px 14px;
            border-radius: 8px;
            font: 600 11px 'DM Sans', sans-serif;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .sla-dismiss-btn:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        @media (max-width: 640px) {
            .sla-modal-card {
                max-height: 94vh;
            }
            .sla-modal-header {
                padding: 16px;
            }
            .sla-modal-body {
                padding: 14px 16px;
            }
            .sla-modal-footer {
                padding: 12px 16px;
            }
            .sla-card-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
            .sla-action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    {{-- MODAL INTERACTIVE SCRIPT --}}
    <script>
        (function() {
            const modal = document.getElementById('slaAlertModal');
            if (!modal) return;

            // Open Modal Helper
            window.openSlaModal = function() {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                if (window.lucide) {
                    lucide.createIcons();
                }
            };

            // Close Modal Helper
            window.closeSlaModal = function(dismissSession = false) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                if (dismissSession) {
                    try {
                        sessionStorage.setItem('sla_modal_dismissed', 'true');
                    } catch (e) {}
                }
            };

            // Filter Tabs Helper
            window.filterSlaCases = function(filter, btn) {
                const tabs = document.querySelectorAll('.sla-tab-btn');
                tabs.forEach(t => t.classList.remove('active'));
                if (btn) btn.classList.add('active');

                const cards = document.querySelectorAll('.sla-case-card');
                cards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    if (filter === 'ALL' || status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            };

            // Auto-open on page load unless user specifically dismissed for this tab session
            document.addEventListener('DOMContentLoaded', function() {
                let isDismissed = false;
                try {
                    isDismissed = sessionStorage.getItem('sla_modal_dismissed') === 'true';
                } catch (e) {}

                if (!isDismissed) {
                    // Small timeout for smooth initial page render before modal appears
                    setTimeout(() => {
                        window.openSlaModal();
                    }, 400);
                }
            });

            // Close on Backdrop Click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    window.closeSlaModal();
                }
            });

            // Close on Escape Key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display !== 'none') {
                    window.closeSlaModal();
                }
            });
        })();
    </script>
@endif
