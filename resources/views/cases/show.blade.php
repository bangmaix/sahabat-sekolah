@extends('layouts.app', [
    'title' => $case->case_number . ' | Sahabat Sekolah',
    'activeNav' => 'inbox',
    'eyebrow' => 'DETAIL LAPORAN KASUS',
    'pageTitle' => 'Kasus ' . $case->case_number,
    'pageSubtitle' => 'Dibuka ' . \Illuminate\Support\Carbon::parse($case->opened_at)->format('d M Y, H:i') . ' · ' . $case->school_name,
])

@section('content')
    <div class="case-header-nav">
        <a class="back-link" href="{{ route('reports.inbox') }}">
            <i data-lucide="arrow-left"></i> Kembali ke Inbox Laporan
        </a>
        <div class="case-heading-actions">
            @if (in_array($case->status, ['RESOLVED', 'CLOSED']))
                <a href="{{ route('cases.print', $case->case_number) }}" target="_blank" class="secondary-button" style="text-decoration: none; padding: 6px 12px; height: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; border-radius: 6px;">
                    <i data-lucide="printer" style="width: 14px; height: 14px;"></i> Cetak Laporan
                </a>
            @endif
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
                        <strong>{{ match ($case->reporter_role) {'VICTIM' => 'Saya korban','WITNESS' => 'Saya saksi',default => 'Mengetahui kejadian'} }}</strong>
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
                        <span class="confidential"><i
                                data-lucide="lock-keyhole"></i>{{ $case->reporter_access_level }}</span>
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
                        <div>
                            <small>Kelas</small>
                            <strong>{{ $case->reporter_class ?: '-' }}</strong>
                        </div>
                        <div>
                            <small>NIS</small>
                            <strong>{{ $case->reporter_student_number ?: '-' }}</strong>
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
                    <form class="participant-form" method="POST"
                        action="{{ route('cases.participants', $case->case_number) }}">
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
                                <label style="font-size: 10px; color: #64748b; font-weight: 500;">Visibilitas
                                    Identitas</label>
                                <style>
                                    .visibility-popover summary::-webkit-details-marker {
                                        display: none;
                                    }

                                    .visibility-popover[open] summary i {
                                        color: #2563eb;
                                    }
                                </style>
                                <details class="visibility-popover" style="position: relative; display: inline-block;">
                                    <summary
                                        style="list-style: none; cursor: pointer; display: flex; align-items: center; color: #94a3b8; outline: none;"
                                        title="Info Visibilitas">
                                        <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                                    </summary>
                                    <div
                                        style="position: absolute; right: 0; bottom: calc(100% + 8px); z-index: 50; width: 280px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 11px; color: #475569; line-height: 1.5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); cursor: default; font-weight: normal; text-transform: none;">
                                        <strong
                                            style="display: block; margin-bottom: 6px; color: #1e293b; font-size: 12px;">Tingkat
                                            Visibilitas</strong>
                                        <ul style="margin: 0; padding-left: 18px; display: grid; gap: 4px;">
                                            <li><b>Terbatas:</b> Identitas disamarkan bagi pihak di luar penanganan utama.
                                            </li>
                                            <li><b>Penuh:</b> Identitas terlihat di seluruh dokumen dan pihak terkait.</li>
                                            <li><b>Sangat sensitif:</b> Hanya dapat dilihat oleh Kepala Sekolah dan Guru BK
                                                yang menangani.</li>
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
                                .risk-popover summary::-webkit-details-marker {
                                    display: none;
                                }

                                .risk-popover[open] summary i {
                                    color: #2563eb;
                                }
                            </style>
                            <details class="risk-popover" style="position: relative; display: inline-block;">
                                <summary
                                    style="list-style: none; cursor: pointer; display: flex; align-items: center; color: #94a3b8; outline: none;"
                                    title="Panduan Penilaian">
                                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                                </summary>
                                <div
                                    style="position: absolute; left: 0; top: calc(100% + 8px); z-index: 50; width: 340px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; font-size: 11px; color: #475569; line-height: 1.5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); cursor: default; font-weight: normal;">
                                    <strong
                                        style="display: block; margin-bottom: 8px; color: #1e293b; font-size: 12px;">Panduan
                                        Penilaian (Total 100 Poin)</strong>
                                    <ul
                                        style="margin: 0; padding-left: 20px; margin-bottom: 12px; display: grid; gap: 4px;">
                                        <li><b>Kategori (0-25):</b> Berdasarkan keparahan kategori & subkategori kejadian.
                                        </li>
                                        <li><b>Keselamatan (0-25):</b> Menilai tingkat ancaman atau bahaya keselamatan
                                            fisik/psikologis.</li>
                                        <li><b>Pengulangan (0-25):</b> Seberapa sering atau potensi kejadian ini berulang di
                                            masa depan.</li>
                                        <li><b>Dampak (0-25):</b> Dampak yang ditimbulkan terhadap korban (trauma, cedera,
                                            dll).</li>
                                    </ul>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <span
                                            style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 4px; font-weight: 600;">0-25:
                                            LOW</span>
                                        <span
                                            style="background: #fef08a; color: #854d0e; padding: 3px 8px; border-radius: 4px; font-weight: 600;">26-50:
                                            MEDIUM</span>
                                        <span
                                            style="background: #fed7aa; color: #9a3412; padding: 3px 8px; border-radius: 4px; font-weight: 600;">51-75:
                                            HIGH</span>
                                        <span
                                            style="background: #fecaca; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-weight: 600;">76-100:
                                            CRITICAL</span>
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
                    <label>Kategori <input type="number" name="category_score" min="0" max="25"
                            value="{{ $riskAssessment->category_score ?? 0 }}" required><small>0–25</small></label>
                    <label>Keselamatan <input type="number" name="safety_score" min="0" max="25"
                            value="{{ $riskAssessment->safety_score ?? 0 }}" required><small>0–25</small></label>
                    <label>Pengulangan <input type="number" name="repetition_score" min="0" max="25"
                            value="{{ $riskAssessment->repetition_score ?? 0 }}" required><small>0–25</small></label>
                    <label>Dampak <input type="number" name="impact_score" min="0" max="25"
                            value="{{ $riskAssessment->impact_score ?? 0 }}" required><small>0–25</small></label>
                    <input class="risk-reason" type="text" name="reason" placeholder="Catatan penilaian (opsional)">
                    <button class="secondary-button" type="submit"><i data-lucide="shield-check"></i> Simpan
                        penilaian</button>
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
                                <small>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y, H:i') }} ·
                                    {{ $item->changed_by }}</small>
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
                            <small>{{ \Illuminate\Support\Carbon::parse($action->created_at)->format('d M Y, H:i') }} ·
                                {{ $action->performed_by }}</small>
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

                @forelse ($notes as $note)
                    <div class="note-row">
                        <span class="activity-icon"><i data-lucide="notebook-tabs"></i></span>
                        <div>
                            <strong>{{ $note->note_type }}</strong>
                            <p>{{ $note->content }}</p>
                            <small>{{ \Illuminate\Support\Carbon::parse($note->created_at)->format('d M Y, H:i') }} ·
                                {{ $note->created_by }}</small>
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
                            @php
                                $mime = $evidence->mime_type ?? '';
                                $isImage = str_starts_with($mime, 'image/');
                                $isVideo = str_starts_with($mime, 'video/');
                                $isAudio = str_starts_with($mime, 'audio/');
                                $isPdf = $mime === 'application/pdf';
                            @endphp
                            <div class="document-row" style="margin-bottom: 12px; align-items: flex-start;">
                                @if($isImage)
                                    <div style="width: 42px; height: 42px; border-radius: 6px; overflow: hidden; flex-shrink: 0; border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <img src="{{ route('cases.documents.download', [$case->case_number, 'evidence', $evidence->id]) }}" alt="Thumb" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @else
                                    <div style="width: 42px; height: 42px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e2e8f0; color: #64748b;">
                                        @if($isVideo)
                                            <i data-lucide="video" style="width: 20px; height: 20px;"></i>
                                        @elseif($isAudio)
                                            <i data-lucide="music" style="width: 20px; height: 20px;"></i>
                                        @elseif($isPdf)
                                            <i data-lucide="file-text" style="width: 20px; height: 20px;"></i>
                                        @else
                                            <i data-lucide="file" style="width: 20px; height: 20px;"></i>
                                        @endif
                                    </div>
                                @endif
                                <div style="display: flex; flex-direction: column; justify-content: center; min-height: 42px;">
                                    <a target="_blank"
                                        href="{{ route('cases.documents.download', [$case->case_number, 'evidence', $evidence->id]) }}">
                                        <strong style="word-break: break-all;">{{ $evidence->file_name }}</strong>
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
                            @php
                                $mime = $document->mime_type ?? '';
                                $isImage = str_starts_with($mime, 'image/');
                                $isVideo = str_starts_with($mime, 'video/');
                                $isAudio = str_starts_with($mime, 'audio/');
                                $isPdf = $mime === 'application/pdf';
                            @endphp
                            <div class="document-row" style="margin-bottom: 12px; align-items: flex-start;">
                                @if($isImage)
                                    <div style="width: 42px; height: 42px; border-radius: 6px; overflow: hidden; flex-shrink: 0; border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <img src="{{ route('cases.documents.download', [$case->case_number, 'resolution', $document->id]) }}" alt="Thumb" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @else
                                    <div style="width: 42px; height: 42px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e2e8f0; color: #16a34a;">
                                        @if($isVideo)
                                            <i data-lucide="video" style="width: 20px; height: 20px;"></i>
                                        @elseif($isAudio)
                                            <i data-lucide="music" style="width: 20px; height: 20px;"></i>
                                        @elseif($isPdf)
                                            <i data-lucide="file-check-2" style="width: 20px; height: 20px;"></i>
                                        @else
                                            <i data-lucide="file-check-2" style="width: 20px; height: 20px;"></i>
                                        @endif
                                    </div>
                                @endif
                                <div style="display: flex; flex-direction: column; justify-content: center; min-height: 42px;">
                                    <a target="_blank"
                                        href="{{ route('cases.documents.download', [$case->case_number, 'resolution', $document->id]) }}">
                                        <strong style="word-break: break-all;">{{ $document->file_name }}</strong>
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
                            <small>{{ \Illuminate\Support\Carbon::parse($escalation->created_at)->format('d M Y, H:i') }} ·
                                {{ $escalation->escalated_by }}</small>
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
                    <form class="participant-form" method="POST"
                        action="{{ route('cases.parent-involvement', $case->case_number) }}">
                        @csrf
                        <div
                            style="grid-column: 1 / -1; display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Tambah pelibatan</span>
                            <style>
                                .parent-popover summary::-webkit-details-marker {
                                    display: none;
                                }

                                .parent-popover[open] summary i {
                                    color: #2563eb;
                                }
                            </style>
                            <details class="parent-popover" style="position: relative; display: inline-block;">
                                <summary
                                    style="list-style: none; cursor: pointer; display: flex; align-items: center; color: #94a3b8; outline: none;"
                                    title="Kenapa butuh pelibatan orang tua?">
                                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                                </summary>
                                <div
                                    style="position: absolute; left: 0; bottom: calc(100% + 8px); z-index: 50; width: 340px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; font-size: 11px; color: #475569; line-height: 1.5; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); cursor: default; font-weight: normal; text-align: left;">
                                    <strong
                                        style="display: block; margin-bottom: 4px; color: #1e293b; font-size: 12px;">Pentingnya
                                        Pelibatan Orang Tua</strong>
                                    <p style="margin: 0 0 10px 0;">Pelibatan diperlukan untuk memastikan transparansi,
                                        memberi dukungan moral, dan menyelaraskan penyelesaian kasus secara holistik.</p>
                                    <strong
                                        style="display: block; margin-bottom: 4px; color: #1e293b; font-size: 11px;">Penjelasan
                                        Status:</strong>
                                    <ul style="margin: 0; padding-left: 16px; display: grid; gap: 4px;">
                                        <li><b>Tidak diperlukan:</b> Kasus ringan atau butuh privasi tinggi, cukup ditangani
                                            di sekolah.</li>
                                        <li><b>Menunggu keputusan:</b> Masih dievaluasi apakah kasus ini butuh campur tangan
                                            orang tua.</li>
                                        <li><b>Disetujui:</b> Pihak sekolah sudah sepakat untuk melibatkan orang tua.</li>
                                        <li><b>Sudah dihubungi:</b> Pesan, telepon, atau surat panggilan sudah dikirimkan ke
                                            orang tua.</li>
                                        <li><b>Selesai:</b> Proses diskusi atau mediasi bersama orang tua telah terlaksana.
                                        </li>
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
                        <button class="secondary-button" type="submit"><i data-lucide="users"></i> Simpan
                            keputusan</button>
                    </form>
                @endif
            </div>
        </section>

        <aside class="case-aside">
            @if ($case->status !== 'CLOSED')
                <div class="action-panel-card">
                    {{-- Header --}}
                    <div class="action-panel-header">
                        <div class="action-panel-header-icon">
                            <i data-lucide="clipboard-check"></i>
                        </div>
                        <div>
                            <h2 class="action-panel-title">Aksi Penanganan</h2>
                            <p class="action-panel-subtitle">Perbarui status, catat perkembangan, dan lampirkan dokumen sekaligus.</p>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="action-current-status">
                        <span class="action-status-dot status-dot-{{ strtolower(str_replace('_','-',$case->status)) }}"></span>
                        <span class="action-status-label">Status sekarang:</span>
                        <span class="action-status-value">{{ str_replace('_', ' ', $case->status) }}</span>
                    </div>

                    <form method="POST" action="{{ route('cases.action', $case->case_number) }}" enctype="multipart/form-data" class="action-terpadu-form">
                        @csrf

                        {{-- STEP 1: Status --}}
                        <div class="action-step" id="step-status">
                            <div class="action-step-header">
                                <div class="action-step-num">1</div>
                                <div>
                                    <strong class="action-step-title">Perbarui Status</strong>
                                    <span class="action-step-tag">Wajib dipilih jika ada perubahan</span>
                                </div>
                            </div>
                            <div class="action-step-body">
                                @if ($case->status === 'PENDING_RESPONSE')
                                    <input type="hidden" name="status" value="UNDER_VERIFICATION">
                                    <div class="action-next-status pending">
                                        <i data-lucide="arrow-right-circle"></i>
                                        <div>
                                            <strong>Beri Respons Awal</strong>
                                            <small>Kasus akan pindah ke tahap Verifikasi</small>
                                        </div>
                                    </div>
                                @elseif ($case->status === 'UNDER_VERIFICATION')
                                    <div class="action-select-wrap">
                                        <i data-lucide="chevrons-up-down" class="action-select-icon"></i>
                                        <select name="status" class="action-select">
                                            <option value="">— Tetap di Verifikasi —</option>
                                            <option value="IN_HANDLING">🔄 Mulai Penanganan</option>
                                            <option value="RESOLVED">✅ Tandai Selesai</option>
                                        </select>
                                    </div>
                                @elseif ($case->status === 'IN_HANDLING')
                                    <div class="action-select-wrap">
                                        <i data-lucide="chevrons-up-down" class="action-select-icon"></i>
                                        <select name="status" class="action-select">
                                            <option value="">— Tetap di Penanganan —</option>
                                            <option value="RESOLVED">✅ Tandai Selesai</option>
                                        </select>
                                    </div>
                                @elseif ($case->status === 'RESOLVED')
                                    @php
                                        $hasResolutionDoc = $resolutionDocuments->where('verification_status', 'VALID')->count() > 0;
                                    @endphp
                                    @if (!$hasResolutionDoc)
                                        <div class="action-warning-box">
                                            <i data-lucide="alert-triangle"></i>
                                            <span>Unggah dokumen penyelesaian (Langkah 3) agar kasus dapat ditutup.</span>
                                        </div>
                                    @endif
                                    <div class="action-select-wrap">
                                        <i data-lucide="chevrons-up-down" class="action-select-icon"></i>
                                        <select name="status" class="action-select">
                                            <option value="">— Tetap di Status Selesai —</option>
                                            <option value="CLOSED">🔒 Tutup Kasus</option>
                                        </select>
                                    </div>
                                @endif

                                <textarea name="status_reason" class="action-textarea" rows="2"
                                    placeholder="Catatan atau alasan perubahan status... (opsional)"></textarea>
                            </div>
                        </div>

                        {{-- DIVIDER --}}
                        <div class="action-divider"><span>dan / atau</span></div>

                        {{-- STEP 2: Catatan --}}
                        <div class="action-step" id="step-note">
                            <div class="action-step-header">
                                <div class="action-step-num note">2</div>
                                <div>
                                    <strong class="action-step-title">Tambah Catatan Internal</strong>
                                    <span class="action-step-tag">Opsional · Hanya Guru BK</span>
                                </div>
                            </div>
                            <div class="action-step-body">
                                <div class="action-chip-group" id="noteTypeChips">
                                    <input type="hidden" name="note_type" id="noteTypeInput" value="">
                                    <button type="button" class="action-chip" data-value="VERIFICATION" onclick="selectChip(this, 'noteTypeInput')">Verifikasi</button>
                                    <button type="button" class="action-chip" data-value="HANDLING" onclick="selectChip(this, 'noteTypeInput')">Penanganan</button>
                                    <button type="button" class="action-chip" data-value="COUNSELING" onclick="selectChip(this, 'noteTypeInput')">Konseling</button>
                                    <button type="button" class="action-chip" data-value="FOLLOW_UP" onclick="selectChip(this, 'noteTypeInput')">Tindak lanjut</button>
                                    <button type="button" class="action-chip" data-value="INTERNAL" onclick="selectChip(this, 'noteTypeInput')">Internal</button>
                                </div>
                                <textarea name="note_content" class="action-textarea" rows="3"
                                    placeholder="Tulis catatan internal untuk tim penanganan..."></textarea>
                            </div>
                        </div>

                        {{-- DIVIDER --}}
                        <div class="action-divider"><span>dan / atau</span></div>

                        {{-- STEP 3: Dokumen --}}
                        <div class="action-step" id="step-doc">
                            <div class="action-step-header">
                                <div class="action-step-num doc">3</div>
                                <div>
                                    <strong class="action-step-title">Unggah Dokumen</strong>
                                    <span class="action-step-tag">Opsional · Maks 10 MB</span>
                                </div>
                            </div>
                            <div class="action-step-body">
                                <div class="action-chip-group" id="docTypeChips">
                                    <input type="hidden" name="document_type" id="docTypeInput" value="">
                                    <button type="button" class="action-chip doc-chip" data-value="EVIDENCE" onclick="selectChip(this, 'docTypeInput')">
                                        <i data-lucide="paperclip" style="width:11px;height:11px;"></i> Bukti kasus
                                    </button>
                                    <button type="button" class="action-chip doc-chip" data-value="RESOLUTION" onclick="selectChip(this, 'docTypeInput')">
                                        <i data-lucide="file-check" style="width:11px;height:11px;"></i> Dokumen penyelesaian
                                    </button>
                                </div>
                                <label class="action-file-drop" id="fileDropZone">
                                    <input type="file" name="document_file" id="docFileInput" class="action-file-hidden"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.mp4,.mov,.mp3,.wav"
                                        onchange="updateFileName(this)">
                                    <div class="action-file-placeholder" id="filePlaceholder">
                                        <i data-lucide="upload-cloud"></i>
                                        <span>Klik atau seret file ke sini</span>
                                        <small>JPG, PNG, PDF, DOC, audio, video</small>
                                    </div>
                                    <div class="action-file-selected" id="fileSelected" style="display:none;">
                                        <i data-lucide="file-check-2"></i>
                                        <span id="fileSelectedName">-</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="action-submit-btn">
                            <i data-lucide="save"></i>
                            Simpan Semua Tindakan
                        </button>
                    </form>
                </div>
            @else
                <div class="case-panel">
                    <div class="done-state">
                        <i data-lucide="shield-check"></i>
                        <strong>Kasus sudah ditutup</strong>
                        <p>Seluruh proses penanganan dan dokumentasi telah selesai.</p>
                    </div>
                </div>
            @endif

            @if (session('user_role') === 'COUNSELOR')
                <div class="case-panel escalation-panel">
                    <h2>Eskalasi manual</h2>
                    <p>Kirim notifikasi prioritas kepada Kepala Sekolah untuk kasus yang membutuhkan perhatian.</p>
                    <form method="POST" action="{{ route('cases.escalate', $case->case_number) }}">
                        @csrf
                        <textarea name="reason" rows="3" minlength="5" required placeholder="Jelaskan alasan eskalasi..."></textarea>
                        <button class="secondary-button" type="submit"><i data-lucide="arrow-up-right"></i> Eskalasikan
                            kasus</button>
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

    <style>
        /* ===== ACTION PANEL CARD ===== */
        .action-panel-card {
            background: #fff;
            border: 1px solid #dce9f7;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(23, 78, 140, .07);
        }

        .action-panel-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 20px 14px;
            background: linear-gradient(135deg, #1565c0 0%, #1877e3 100%);
            color: #fff;
        }

        .action-panel-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            display: grid;
            place-items: center;
            flex: none;
            backdrop-filter: blur(4px);
        }

        .action-panel-header-icon svg {
            width: 20px;
        }

        .action-panel-title {
            margin: 0;
            font: 700 14px 'Plus Jakarta Sans';
            color: #fff !important;
        }

        .action-panel-subtitle {
            margin: 3px 0 0;
            font-size: 10px;
            color: rgba(255, 255, 255, .75);
            line-height: 1.4;
        }

        .action-current-status {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 10px 20px;
            background: #f0f7ff;
            border-bottom: 1px solid #dde9f7;
            font-size: 10px;
        }

        .action-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex: none;
        }

        .status-dot-pending-response { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
        .status-dot-under-verification { background: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
        .status-dot-in-handling { background: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,.2); }
        .status-dot-resolved { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }

        .action-status-label { color: #8097ae; }
        .action-status-value { color: #215c8a; font-weight: 700; }

        /* ===== FORM ===== */
        .action-terpadu-form {
            padding: 16px 20px 20px;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* ===== STEP ===== */
        .action-step {
            border: 1px solid #e8f0f9;
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow .2s, border-color .2s;
        }

        .action-step:hover {
            border-color: #b8d4f0;
            box-shadow: 0 2px 10px rgba(23, 78, 140, .06);
        }

        .action-step:focus-within {
            border-color: #4d9ae4;
            box-shadow: 0 0 0 3px rgba(77, 154, 228, .12);
        }

        .action-step-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            background: #f7fafd;
            border-bottom: 1px solid #edf3f9;
            cursor: default;
        }

        .action-step-num {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: #1877e3;
            color: #fff;
            font: 700 10px 'Plus Jakarta Sans';
            display: grid;
            place-items: center;
            flex: none;
        }

        .action-step-num.note { background: #7c3aed; }
        .action-step-num.doc  { background: #059669; }

        .action-step-title {
            color: #1b3f6a;
            font-size: 11px;
            display: block;
        }

        .action-step-tag {
            color: #8da5be;
            font-size: 9px;
            display: block;
            margin-top: 1px;
        }

        .action-step-body {
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* ===== NEXT STATUS DISPLAY ===== */
        .action-next-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: linear-gradient(135deg, #e8f5ff, #f0f9ff);
            border: 1px solid #bdddf5;
            border-radius: 8px;
            color: #1877e3;
        }

        .action-next-status svg { width: 18px; flex: none; }
        .action-next-status strong { display: block; font-size: 11px; }
        .action-next-status small { color: #5f8bb0; font-size: 9px; }

        /* ===== SELECT ===== */
        .action-select-wrap {
            position: relative;
        }

        .action-select-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            color: #8da5be;
            pointer-events: none;
        }

        .action-select {
            width: 100%;
            padding: 9px 32px 9px 11px;
            border: 1px solid #dde9f5;
            border-radius: 7px;
            background: #fbfdff;
            color: #2d5a82;
            font: 500 11px 'DM Sans';
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }

        .action-select:focus {
            outline: none;
            border-color: #4d9ae4;
            box-shadow: 0 0 0 3px rgba(77, 154, 228, .12);
        }

        /* ===== TEXTAREA ===== */
        .action-textarea {
            width: 100%;
            border: 1px solid #dde9f5;
            border-radius: 7px;
            padding: 9px 11px;
            color: #315675;
            background: #fbfdff;
            font: 400 11px 'DM Sans';
            resize: vertical;
            line-height: 1.5;
            transition: border-color .15s, box-shadow .15s;
        }

        .action-textarea:focus {
            outline: none;
            border-color: #4d9ae4;
            box-shadow: 0 0 0 3px rgba(77, 154, 228, .12);
        }

        .action-textarea::placeholder { color: #a8bac9; }

        /* ===== WARNING BOX ===== */
        .action-warning-box {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 10px 12px;
            background: #fff8ec;
            border: 1px solid #fddcab;
            border-radius: 7px;
            color: #92590d;
            font-size: 10px;
            line-height: 1.5;
        }

        .action-warning-box svg { width: 14px; flex: none; margin-top: 1px; color: #d97706; }

        /* ===== CHIPS ===== */
        .action-chip-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .action-chip {
            padding: 5px 10px;
            border: 1px solid #d6e6f4;
            border-radius: 20px;
            background: #f3f9ff;
            color: #4a7499;
            font: 500 9px 'DM Sans';
            cursor: pointer;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .action-chip:hover {
            border-color: #4d9ae4;
            background: #eaf4ff;
            color: #1877e3;
        }

        .action-chip.selected {
            background: #1877e3;
            border-color: #1877e3;
            color: #fff;
            box-shadow: 0 2px 6px rgba(24, 119, 227, .25);
        }

        /* ===== FILE UPLOAD ===== */
        .action-file-drop {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #c8dff0;
            border-radius: 9px;
            padding: 18px;
            cursor: pointer;
            transition: all .2s;
            background: #f7fbff;
            min-height: 80px;
        }

        .action-file-drop:hover {
            border-color: #4d9ae4;
            background: #eef6ff;
        }

        .action-file-hidden {
            display: none;
        }

        .action-file-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #7fa3c0;
            text-align: center;
        }

        .action-file-placeholder svg { width: 24px; color: #4d9ae4; }
        .action-file-placeholder span { font-size: 10px; font-weight: 600; color: #4a7499; }
        .action-file-placeholder small { font-size: 9px; color: #9ab5c9; }

        .action-file-selected {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1a9a72;
            font-size: 10px;
            font-weight: 600;
        }

        .action-file-selected svg { width: 18px; }

        /* ===== DIVIDER ===== */
        .action-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            color: #b0c4d6;
            font-size: 9px;
            letter-spacing: .04em;
        }

        .action-divider::before,
        .action-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5eff8;
        }

        /* ===== SUBMIT ===== */
        .action-submit-btn {
            width: 100%;
            padding: 13px;
            border: 0;
            border-radius: 9px;
            background: linear-gradient(135deg, #1565c0 0%, #1877e3 100%);
            color: #fff;
            font: 700 12px 'Plus Jakarta Sans';
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 14px;
            box-shadow: 0 6px 18px rgba(24, 120, 227, .3);
            transition: box-shadow .2s, transform .15s;
        }

        .action-submit-btn:hover {
            box-shadow: 0 8px 24px rgba(24, 120, 227, .4);
            transform: translateY(-1px);
        }

        .action-submit-btn:active { transform: translateY(0); }
        .action-submit-btn svg { width: 16px; }
    </style>

    <script>
        function selectChip(el, inputId) {
            const group = el.closest('.action-chip-group');
            group.querySelectorAll('.action-chip').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById(inputId).value = el.dataset.value;
        }

        function updateFileName(input) {
            const placeholder = document.getElementById('filePlaceholder');
            const selected = document.getElementById('fileSelected');
            const name = document.getElementById('fileSelectedName');
            if (input.files && input.files[0]) {
                name.textContent = input.files[0].name;
                placeholder.style.display = 'none';
                selected.style.display = 'flex';
            } else {
                placeholder.style.display = 'flex';
                selected.style.display = 'none';
            }
        }

        // Re-init Lucide after dynamic render
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
@endsection

