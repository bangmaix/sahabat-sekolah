@extends('layouts.app', [
    'title' => $case->case_number . ' | Sahabat Sekolah',
    'activeNav' => 'inbox',
    'eyebrow' => 'DETAIL LAPORAN KASUS',
    'pageTitle' => 'Kasus ' . $case->case_number,
    'pageSubtitle' => 'Dibuka ' . \Illuminate\Support\Carbon::parse($case->opened_at)->format('d M Y, H:i') . ' · ' . $case->school_name
])

@section('content')
<div class="case-header-nav">
    <a class="back-link" href="{{ route('reports.inbox') }}">
        <i data-lucide="arrow-left"></i> Kembali ke Inbox Laporan
    </a>
    <div class="case-heading-actions">
        <span class="status {{ strtolower($case->status) }}">{{ str_replace('_', ' ', $case->status) }}</span>
        <span class="priority {{ strtolower($case->risk_level) }}">Risiko {{ $case->risk_level }}</span>
    </div>
</div>

<main class="case-grid">
    <section class="case-main">
        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Informasi laporan</h2>
                <span class="confidential">
                    <i data-lucide="lock-keyhole"></i>
                    {{ $case->identity_mode === 'ANONYMOUS' ? 'Pelapor anonim' : 'Identitas terbatas' }}
                </span>
            </div>
            <div class="detail-grid">
                <div>
                    <small>Peran pelapor</small>
                    <strong>{{ match($case->reporter_role) { 'VICTIM' => 'Saya korban', 'WITNESS' => 'Saya saksi', default => 'Mengetahui kejadian' } }}</strong>
                </div>
                <div>
                    <small>Kategori</small>
                    <strong>{{ $case->category }}</strong>
                </div>
                <div>
                    <small>Status SLA</small>
                    <strong class="sla-{{ strtolower($case->sla_status) }}">{{ $case->sla_status }}</strong>
                </div>
                <div>
                    <small>Batas respons awal</small>
                    <strong>{{ \Illuminate\Support\Carbon::parse($case->response_deadline)->format('d M Y, H:i') }}</strong>
                </div>
            </div>
            <div class="description-block">
                <small>Kronologi laporan</small>
                <p>{{ $case->description }}</p>
            </div>
        </div>

        @if (session('user_role') === 'COUNSELOR' && $case->reporter_name)
            <div class="case-panel identity-panel">
                <div class="case-panel-heading">
                    <h2>Identitas pelapor</h2>
                    <span class="confidential"><i data-lucide="lock-keyhole"></i>{{ $case->reporter_access_level }}</span>
                </div>
                <div class="detail-grid">
                    <div>
                        <small>Nama lengkap</small>
                        <strong>{{ $case->reporter_name }}</strong>
                    </div>
                    <div>
                        <small>Kontak</small>
                        <strong>{{ $case->reporter_contact ?: 'Tidak diberikan' }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Pihak terkait</h2>
                <span class="case-count">{{ $participants->count() }} pihak</span>
            </div>
            @forelse ($participants as $participant)
                <div class="participant-row">
                    <span class="participant-type">{{ $participant->participant_type }}</span>
                    <div>
                        <strong>{{ session('user_role') === 'COUNSELOR' ? $participant->display_name : 'Identitas dibatasi' }}</strong>
                        <small>{{ $participant->identity_visibility }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-state">Belum ada pihak terkait tercatat.</p>
            @endforelse

            @if (session('user_role') === 'COUNSELOR')
                <form class="participant-form" method="POST" action="{{ route('cases.participants', $case->case_number) }}">
                    @csrf
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 10px; color: #64748b; font-weight: 500;">Peran</label>
                        <select name="participant_type">
                            <option value="VICTIM">Korban</option>
                            <option value="REPORTER">Pelapor</option>
                            <option value="WITNESS">Saksi</option>
                            <option value="ALLEGED_PERPETRATOR">Terduga pelaku</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 10px; color: #64748b; font-weight: 500;">Nama Pihak Terkait</label>
                        <input name="display_name" required placeholder="Nama lengkap">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <label style="font-size: 10px; color: #64748b; font-weight: 500;">Visibilitas Identitas</label>
                            <style>
                                .visibility-popover summary::-webkit-details-marker { display: none; }
                                .visibility-popover[open] summary i { color: #2563eb; }
                            </style>
                            <details class="visibility-popover" style="position: relative; display: inline-block;">
                                <summary style="list-style: none; cursor: pointer; display: flex; align-items: center; color: #94a3b8; outline: none;" title="Info Visibilitas">
                                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                                </summary>
                                <div style="position: absolute; right: 0; bottom: calc(100% + 8px); z-index: 50; width: 280px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 11px; color: #475569; line-height: 1.5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); cursor: default; font-weight: normal; text-transform: none;">
                                    <strong style="display: block; margin-bottom: 6px; color: #1e293b; font-size: 12px;">Tingkat Visibilitas</strong>
                                    <ul style="margin: 0; padding-left: 18px; display: grid; gap: 4px;">
                                        <li><b>Terbatas:</b> Identitas disamarkan bagi pihak di luar penanganan utama.</li>
                                        <li><b>Penuh:</b> Identitas terlihat di seluruh dokumen dan pihak terkait.</li>
                                        <li><b>Sangat sensitif:</b> Hanya dapat dilihat oleh Kepala Sekolah dan Guru BK yang menangani.</li>
                                    </ul>
                                </div>
                            </details>
                        </div>
                        <select name="identity_visibility">
                            <option value="CASE_RESTRICTED">Terbatas</option>
                            <option value="CASE_FULL">Penuh</option>
                            <option value="CASE_SENSITIVE">Sangat sensitif</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                        <button class="secondary-button" type="submit" style="width: 100%;">
                            <i data-lucide="user-plus"></i> Tambah pihak
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <div class="case-panel risk-panel">
            <div class="case-panel-heading">
                <div>
                    <h2 style="display: flex; align-items: center; gap: 8px;">
                        Penilaian risiko
                        <style>
                            .risk-popover summary::-webkit-details-marker { display: none; }
                            .risk-popover[open] summary i { color: #2563eb; }
                        </style>
                        <details class="risk-popover" style="position: relative; display: inline-block;">
                            <summary style="list-style: none; cursor: pointer; display: flex; align-items: center; color: #94a3b8; outline: none;" title="Panduan Penilaian">
                                <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                            </summary>
                            <div style="position: absolute; left: 0; top: calc(100% + 8px); z-index: 50; width: 340px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; font-size: 11px; color: #475569; line-height: 1.5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); cursor: default; font-weight: normal;">
                                <strong style="display: block; margin-bottom: 8px; color: #1e293b; font-size: 12px;">Panduan Penilaian (Total 100 Poin)</strong>
                                <ul style="margin: 0; padding-left: 20px; margin-bottom: 12px; display: grid; gap: 4px;">
                                    <li><b>Kategori (0-25):</b> Berdasarkan keparahan kategori & subkategori kejadian.</li>
                                    <li><b>Keselamatan (0-25):</b> Menilai tingkat ancaman atau bahaya keselamatan fisik/psikologis.</li>
                                    <li><b>Pengulangan (0-25):</b> Seberapa sering atau potensi kejadian ini berulang di masa depan.</li>
                                    <li><b>Dampak (0-25):</b> Dampak yang ditimbulkan terhadap korban (trauma, cedera, dll).</li>
                                </ul>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <span style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 4px; font-weight: 600;">0-25: LOW</span>
                                    <span style="background: #fef08a; color: #854d0e; padding: 3px 8px; border-radius: 4px; font-weight: 600;">26-50: MEDIUM</span>
                                    <span style="background: #fed7aa; color: #9a3412; padding: 3px 8px; border-radius: 4px; font-weight: 600;">51-75: HIGH</span>
                                    <span style="background: #fecaca; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-weight: 600;">76-100: CRITICAL</span>
                                </div>
                            </div>
                        </details>
                    </h2>
                    <p class="panel-subtitle">Skor tersimpan sebagai bagian dari histori kasus.</p>
                </div>
                @if ($riskAssessment)
                    <strong class="risk-score">{{ $riskAssessment->final_score }} / 100</strong>
                @endif
            </div>
            <form class="risk-form" method="POST" action="{{ route('cases.risk', $case->case_number) }}">
                @csrf
                <label>Kategori <input type="number" name="category_score" min="0" max="25" value="{{ $riskAssessment->category_score ?? 0 }}" required><small>0–25</small></label>
                <label>Keselamatan <input type="number" name="safety_score" min="0" max="25" value="{{ $riskAssessment->safety_score ?? 0 }}" required><small>0–25</small></label>
                <label>Pengulangan <input type="number" name="repetition_score" min="0" max="25" value="{{ $riskAssessment->repetition_score ?? 0 }}" required><small>0–25</small></label>
                <label>Dampak <input type="number" name="impact_score" min="0" max="25" value="{{ $riskAssessment->impact_score ?? 0 }}" required><small>0–25</small></label>
                <input class="risk-reason" type="text" name="reason" placeholder="Catatan penilaian (opsional)">
                <button class="secondary-button" type="submit"><i data-lucide="shield-check"></i> Simpan penilaian</button>
            </form>
        </div>

        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Alur penanganan</h2>
                <span class="case-count">{{ $history->count() }} aktivitas tercatat</span>
            </div>
            <div class="timeline">
                <div class="timeline-item current">
                    <span class="timeline-dot"></span>
                    <div>
                        <strong>Laporan masuk</strong>
                        <small>{{ \Illuminate\Support\Carbon::parse($case->submitted_at)->format('d M Y, H:i') }}</small>
                        <p>Laporan diterima oleh sistem dan masuk ke antrean Guru BK.</p>
                    </div>
                </div>
                @foreach ($history as $item)
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            <strong>{{ str_replace('_', ' ', $item->new_status) }}</strong>
                            <small>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y, H:i') }} · {{ $item->changed_by }}</small>
                            <p>{{ $item->reason ?: 'Status kasus diperbarui.' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Aktivitas terakhir</h2>
                <span class="case-count">Audit trail</span>
            </div>
            @forelse ($actions as $action)
                <div class="activity-row">
                    <span class="activity-icon"><i data-lucide="check-check"></i></span>
                    <div>
                        <strong>{{ $action->action_type }}</strong>
                        <p>{{ $action->description }}</p>
                        <small>{{ \Illuminate\Support\Carbon::parse($action->created_at)->format('d M Y, H:i') }} · {{ $action->performed_by }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-state">Belum ada tindakan tercatat.</p>
            @endforelse
        </div>

        <div class="case-panel notes-panel">
            <div class="case-panel-heading">
                <h2>Catatan kasus</h2>
                <span class="case-count">Internal Guru BK</span>
            </div>
            <form class="note-form" method="POST" action="{{ route('cases.notes', $case->case_number) }}">
                @csrf
                <select name="note_type">
                    <option value="VERIFICATION">Verifikasi</option>
                    <option value="HANDLING">Penanganan</option>
                    <option value="COUNSELING">Konseling</option>
                    <option value="FOLLOW_UP">Tindak lanjut</option>
                    <option value="INTERNAL">Internal</option>
                </select>
                <textarea name="content" rows="3" minlength="5" required placeholder="Tulis catatan internal untuk tim penanganan..."></textarea>
                <button class="secondary-button" type="submit"><i data-lucide="notebook-pen"></i> Simpan catatan</button>
            </form>
            @forelse ($notes as $note)
                <div class="note-row">
                    <span class="activity-icon"><i data-lucide="notebook-tabs"></i></span>
                    <div>
                        <strong>{{ $note->note_type }}</strong>
                        <p>{{ $note->content }}</p>
                        <small>{{ \Illuminate\Support\Carbon::parse($note->created_at)->format('d M Y, H:i') }} · {{ $note->created_by }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-state">Belum ada catatan internal.</p>
            @endforelse
        </div>

        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Bukti & dokumen</h2>
                <span class="case-count">{{ $evidences->count() + $resolutionDocuments->count() }} file</span>
            </div>
            <div class="document-columns">
                <div>
                    <small class="document-label">Bukti kasus</small>
                    @forelse ($evidences as $evidence)
                        <div class="document-row">
                            <i data-lucide="paperclip"></i>
                            <div>
                                <a href="{{ route('cases.documents.download', [$case->case_number, 'evidence', $evidence->id]) }}">
                                    <strong>{{ $evidence->file_name }}</strong>
                                </a>
                                <small>{{ number_format(($evidence->file_size ?? 0) / 1024, 0) }} KB</small>
                            </div>
                        </div>
                    @empty
                        <p class="empty-state">Belum ada bukti.</p>
                    @endforelse
                </div>
                <div>
                    <small class="document-label">Dokumen penyelesaian</small>
                    @forelse ($resolutionDocuments as $document)
                        <div class="document-row">
                            <i data-lucide="file-check-2"></i>
                            <div>
                                <a href="{{ route('cases.documents.download', [$case->case_number, 'resolution', $document->id]) }}">
                                    <strong>{{ $document->file_name }}</strong>
                                </a>
                                <small>Valid · {{ $document->uploaded_by }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="empty-state">Belum ada dokumen.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Riwayat eskalasi</h2>
                <span class="case-count">{{ $escalations->count() }} eskalasi</span>
            </div>
            @forelse ($escalations as $escalation)
                <div class="activity-row">
                    <span class="activity-icon"><i data-lucide="arrow-up-right"></i></span>
                    <div>
                        <strong>{{ $escalation->escalation_type }} · {{ $escalation->notified_to }}</strong>
                        <p>{{ $escalation->reason }}</p>
                        <small>{{ \Illuminate\Support\Carbon::parse($escalation->created_at)->format('d M Y, H:i') }} · {{ $escalation->escalated_by }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-state">Belum ada eskalasi.</p>
            @endforelse
        </div>

        <div class="case-panel">
            <div class="case-panel-heading">
                <h2>Pelibatan orang tua</h2>
                <span class="case-count">{{ $parentInvolvements->count() }} pihak</span>
            </div>
            @forelse ($parentInvolvements as $involvement)
                <div class="participant-row">
                    <span class="participant-type">{{ $involvement->status }}</span>
                    <div>
                        <strong>{{ session('user_role') === 'COUNSELOR' ? $involvement->parent_name : 'Identitas dibatasi' }}</strong>
                        <small>{{ $involvement->reason ?: 'Tidak ada catatan alasan.' }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-state">Belum ada keputusan pelibatan orang tua.</p>
            @endforelse
            @if (session('user_role') === 'COUNSELOR')
                <form class="participant-form" method="POST" action="{{ route('cases.parent-involvement', $case->case_number) }}">
                    @csrf
                    <div style="grid-column: 1 / -1; display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                        <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Tambah pelibatan</span>
                        <style>
                            .parent-popover summary::-webkit-details-marker { display: none; }
                            .parent-popover[open] summary i { color: #2563eb; }
                        </style>
                        <details class="parent-popover" style="position: relative; display: inline-block;">
                            <summary style="list-style: none; cursor: pointer; display: flex; align-items: center; color: #94a3b8; outline: none;" title="Kenapa butuh pelibatan orang tua?">
                                <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                            </summary>
                            <div style="position: absolute; left: 0; bottom: calc(100% + 8px); z-index: 50; width: 340px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; font-size: 11px; color: #475569; line-height: 1.5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); cursor: default; font-weight: normal; text-align: left;">
                                <strong style="display: block; margin-bottom: 4px; color: #1e293b; font-size: 12px;">Pentingnya Pelibatan Orang Tua</strong>
                                <p style="margin: 0 0 10px 0;">Pelibatan diperlukan untuk memastikan transparansi, memberi dukungan moral, dan menyelaraskan penyelesaian kasus secara holistik.</p>
                                <strong style="display: block; margin-bottom: 4px; color: #1e293b; font-size: 11px;">Penjelasan Status:</strong>
                                <ul style="margin: 0; padding-left: 16px; display: grid; gap: 4px;">
                                    <li><b>Tidak diperlukan:</b> Kasus ringan atau butuh privasi tinggi, cukup ditangani di sekolah.</li>
                                    <li><b>Menunggu keputusan:</b> Masih dievaluasi apakah kasus ini butuh campur tangan orang tua.</li>
                                    <li><b>Disetujui:</b> Pihak sekolah sudah sepakat untuk melibatkan orang tua.</li>
                                    <li><b>Sudah dihubungi:</b> Pesan, telepon, atau surat panggilan sudah dikirimkan ke orang tua.</li>
                                    <li><b>Selesai:</b> Proses diskusi atau mediasi bersama orang tua telah terlaksana.</li>
                                </ul>
                            </div>
                        </details>
                    </div>
                    <input name="parent_name" required placeholder="Nama orang tua">
                    <input name="parent_contact" placeholder="Kontak opsional">
                    <select name="status">
                        <option value="NOT_REQUIRED">Tidak diperlukan</option>
                        <option value="PENDING">Menunggu keputusan</option>
                        <option value="APPROVED">Disetujui</option>
                        <option value="CONTACTED">Sudah dihubungi</option>
                        <option value="COMPLETED">Selesai</option>
                    </select>
                    <input name="reason" placeholder="Alasan/catatan">
                    <button class="secondary-button" type="submit"><i data-lucide="users"></i> Simpan keputusan</button>
                </form>
            @endif
        </div>
    </section>

    <aside class="case-aside">
        <div class="case-panel quick-panel">
            <h2>Perbarui status</h2>
            <p class="quick-help">Pilih langkah berikutnya sesuai proses penanganan kasus.</p>
            @if ($case->status === 'PENDING_RESPONSE')
                <form method="POST" action="{{ route('cases.status', $case->case_number) }}">
                    @csrf
                    <input type="hidden" name="status" value="UNDER_VERIFICATION">
                    <textarea name="reason" rows="2" placeholder="Catatan respons awal (opsional)"></textarea>
                    <button class="primary-button" type="submit"><i data-lucide="message-circle"></i> Beri respons awal</button>
                </form>
            @elseif ($case->status === 'UNDER_VERIFICATION')
                <form method="POST" action="{{ route('cases.status', $case->case_number) }}" style="display: flex; flex-direction: column; gap: 12px;">
                    @csrf
                    <div>
                        <select name="status" required>
                            <option value="">Pilih tindakan selanjutnya...</option>
                            <option value="IN_HANDLING">Mulai penanganan</option>
                            <option value="RESOLVED">Tandai selesai</option>
                        </select>
                    </div>
                    <textarea name="reason" rows="2" placeholder="Catatan atau alasan (opsional)..."></textarea>
                    <button class="primary-button" type="submit"><i data-lucide="arrow-right-circle"></i> Perbarui status</button>
                </form>
            @elseif ($case->status === 'IN_HANDLING')
                <form method="POST" action="{{ route('cases.status', $case->case_number) }}">
                    @csrf
                    <input type="hidden" name="status" value="RESOLVED">
                    <textarea name="reason" rows="2" placeholder="Ringkasan penyelesaian"></textarea>
                    <button class="primary-button" type="submit"><i data-lucide="circle-check"></i> Tandai selesai</button>
                </form>
            @elseif ($case->status === 'RESOLVED')
                @php
                    $hasResolutionDoc = $resolutionDocuments->where('verification_status', 'VALID')->count() > 0;
                @endphp
                <form method="POST" action="{{ route('cases.status', $case->case_number) }}">
                    @csrf
                    <input type="hidden" name="status" value="CLOSED">
                    @if (!$hasResolutionDoc)
                        <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; margin-bottom: 12px; font-size: 12px; color: #991b1b; line-height: 1.4;">
                            <i data-lucide="alert-circle" style="width: 14px; height: 14px; display: inline-block; vertical-align: text-bottom; margin-right: 4px;"></i>
                            Anda wajib mengunggah dokumen penyelesaian terlebih dahulu.
                        </div>
                    @endif
                    <button class="primary-button" type="submit" @disabled(!$hasResolutionDoc) style="{{ !$hasResolutionDoc ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                        <i data-lucide="lock"></i> Tutup kasus
                    </button>
                </form>
            @else
                <div class="done-state">
                    <i data-lucide="shield-check"></i>
                    <strong>Kasus sudah ditutup</strong>
                    <p>Seluruh proses penanganan dan dokumentasi telah selesai.</p>
                </div>
            @endif
        </div>

        <div class="case-panel upload-panel">
            <h2>Unggah dokumen</h2>
            <form method="POST" action="{{ route('cases.documents', $case->case_number) }}" enctype="multipart/form-data">
                @csrf
                <select name="document_type">
                    <option value="EVIDENCE">Bukti kasus</option>
                    <option value="RESOLUTION">Dokumen penyelesaian</option>
                </select>
                <input type="file" name="file" required>
                <button class="secondary-button" type="submit"><i data-lucide="upload"></i> Unggah file</button>
            </form>
            <small>JPG, PNG, PDF, DOC, audio, atau video. Maksimal 10 MB.</small>
        </div>

        @if (session('user_role') === 'COUNSELOR')
            <div class="case-panel escalation-panel">
                <h2>Eskalasi manual</h2>
                <p>Kirim notifikasi prioritas kepada Kepala Sekolah untuk kasus yang membutuhkan perhatian.</p>
                <form method="POST" action="{{ route('cases.escalate', $case->case_number) }}">
                    @csrf
                    <textarea name="reason" rows="3" minlength="5" required placeholder="Jelaskan alasan eskalasi..."></textarea>
                    <button class="secondary-button" type="submit"><i data-lucide="arrow-up-right"></i> Eskalasikan kasus</button>
                </form>
            </div>
        @endif

        <div class="case-panel privacy-panel">
            <i data-lucide="shield-check"></i>
            <div>
                <strong>Perlindungan identitas aktif</strong>
                <p>Akses kasus ini tercatat dalam audit log dan hanya tersedia untuk pihak berwenang.</p>
            </div>
        </div>
    </aside>
</main>
@endsection
