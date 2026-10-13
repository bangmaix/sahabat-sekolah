<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kasus - {{ $case->case_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #333; margin: 0; padding: 20px; background: #f3f4f6; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-radius: 8px; }
        .header { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; color: #0f172a; }
        .header p { margin: 5px 0 0; color: #64748b; font-weight: 500; }
        .section { margin-bottom: 30px; }
        .section h2 { font-size: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 15px; color: #0f172a; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .field { margin-bottom: 10px; }
        .field label { display: block; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 4px; }
        .field div { font-size: 14px; font-weight: 500; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 13px; color: #1e293b; }
        th { background-color: #f8fafc; font-weight: 700; text-transform: uppercase; font-size: 11px; color: #475569; }
        
        @media print {
            body { padding: 0; background: #fff; }
            .container { max-width: 100%; width: 100%; margin: 0; padding: 0; box-shadow: none; border-radius: 0; }
            .print-btn { display: none !important; }
            @page { margin: 2cm; }
        }
        
        .print-btn { display: block; margin: 0 auto 30px; padding: 12px 24px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold; transition: background 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .print-btn:hover { background: #1d4ed8; }
        
        .desc-box { background: #f8fafc; padding: 15px; border-left: 4px solid #94a3b8; font-size: 13px; color: #334155; white-space: pre-line; border-radius: 0 4px 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <button class="print-btn" onclick="window.print()">🖨️ Cetak Dokumen</button>
        
        <div class="header">
            <h1>Laporan Resmi Penanganan Kasus</h1>
            <p>Sahabat Sekolah - Laporan Rahasia</p>
        </div>
        
        <div class="section">
            <h2>Informasi Laporan</h2>
            <div class="grid">
                <div class="field"><label>Nomor Kasus</label><div>{{ $case->case_number }}</div></div>
                <div class="field"><label>Tanggal Dibuka</label><div>{{ \Illuminate\Support\Carbon::parse($case->opened_at)->format('d F Y, H:i') }}</div></div>
                <div class="field"><label>Status Akhir</label><div>{{ str_replace('_', ' ', $case->status) }}</div></div>
                <div class="field"><label>Risiko Kasus</label><div>{{ $case->risk_level }}</div></div>
                <div class="field"><label>Peran Pelapor</label><div>{{ match($case->reporter_role) { 'VICTIM' => 'Korban', 'WITNESS' => 'Saksi', default => 'Mengetahui' } }}</div></div>
                <div class="field"><label>Kategori</label><div>{{ $case->category }}</div></div>
                <div class="field"><label>Sekolah</label><div>{{ $case->school_name }}</div></div>
                <div class="field"><label>Status SLA</label><div>{{ $case->sla_status }}</div></div>
            </div>
            
            <div class="field" style="margin-top: 20px;">
                <label>Deskripsi Kasus / Kronologi</label>
                <div class="desc-box">{{ $case->description }}</div>
            </div>
        </div>

        <div class="section">
            <h2>Pihak Terkait</h2>
            @if($participants->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Peran</th>
                            <th>Nama Lengkap</th>
                            <th>Visibilitas Identitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $p)
                            <tr>
                                <td>{{ str_replace('_', ' ', $p->participant_type) }}</td>
                                <td><strong>{{ $p->display_name }}</strong></td>
                                <td>{{ str_replace('_', ' ', $p->identity_visibility) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-style: italic; font-size: 13px;">Tidak ada pihak terkait yang dicatat.</p>
            @endif
        </div>

        <div class="section">
            <h2>Tindakan Penanganan (Catatan Internal)</h2>
            @if($notes->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th width="20%">Tanggal</th>
                            <th width="20%">Jenis Tindakan</th>
                            <th width="60%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notes as $note)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($note->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $note->note_type }}</td>
                                <td>{{ $note->content }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-style: italic; font-size: 13px;">Tidak ada catatan tindakan penanganan.</p>
            @endif
        </div>

        <div class="section">
            <h2>Riwayat Perubahan Status (Log)</h2>
            @if($history->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th width="20%">Waktu</th>
                            <th width="25%">Status Baru</th>
                            <th width="25%">Diperbarui Oleh</th>
                            <th width="30%">Catatan Tambahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $item)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                                <td><strong>{{ str_replace('_', ' ', $item->new_status) }}</strong></td>
                                <td>{{ $item->changed_by }}</td>
                                <td>{{ $item->reason ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-style: italic; font-size: 13px;">Tidak ada riwayat perubahan status.</p>
            @endif
        </div>
        
        <div class="section" style="margin-top: 60px; text-align: right;">
            <p style="margin-bottom: 80px; font-size: 13px; color: #475569;">Dokumen resmi dicetak secara otomatis pada {{ now()->format('d F Y, H:i') }}</p>
            <p style="font-weight: bold; margin: 0; color: #1e293b;">_____________________________</p>
            <p style="margin: 8px 0 0; font-weight: bold; color: #0f172a;">{{ session('user_name', 'Petugas Sahabat Sekolah') }}</p>
            <p style="font-size: 11px; color: #64748b; margin: 0;">ID Pengguna: {{ session('user_id', '-') }}</p>
        </div>
    </div>
    
    <script>
        // Tampilkan dialog cetak secara otomatis ketika halaman dimuat
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
