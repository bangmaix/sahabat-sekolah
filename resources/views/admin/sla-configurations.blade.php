@extends('layouts.app', [
    'title' => 'Pengaturan SLA | Sahabat Sekolah',
    'activeNav' => 'admin.sla',
    'pageTitle' => 'Konfigurasi SLA (Service Level Agreement)',
    'pageSubtitle' => 'Kelola batasan waktu respons dan penyelesaian kasus berdasarkan tingkat risiko.'
])

@section('content')
<div class="content-grid" style="grid-template-columns: 1fr;">
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Daftar SLA</h3>
                <p>Ubah konfigurasi waktu untuk setiap level risiko</p>
            </div>
        </div>
        
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tingkat Risiko</th>
                        <th>Batas Waktu Respons (Jam)</th>
                        <th>Batas Waktu Penyelesaian (Hari)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($configs as $config)
                    <tr>
                        <td>
                            <span class="priority {{ strtolower($config->risk_level) }}">{{ $config->risk_level }}</span>
                        </td>
                        <td>{{ $config->response_time_hours }} Jam</td>
                        <td>{{ $config->resolution_time_days }} Hari</td>
                        <td>
                            <button class="view-button" onclick="document.getElementById('edit-modal-{{ $config->id }}').style.display='flex'">
                                Edit <i data-lucide="edit"></i>
                            </button>

                            <!-- Edit Modal -->
                            <div id="edit-modal-{{ $config->id }}" class="modal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:999;">
                                <div class="panel" style="width: 400px; max-width: 90%;">
                                    <div class="panel-heading">
                                        <h3>Edit SLA - {{ $config->risk_level }}</h3>
                                        <button class="icon-button" onclick="document.getElementById('edit-modal-{{ $config->id }}').style.display='none'">
                                            <i data-lucide="x"></i>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.sla-configurations.update', $config->id) }}" method="POST" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                                        @csrf
                                        <div class="form-group">
                                            <label>Waktu Respons (Jam)</label>
                                            <input type="number" name="response_time_hours" value="{{ $config->response_time_hours }}" min="1" max="720" required class="input-field" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                                        </div>
                                        <div class="form-group">
                                            <label>Waktu Penyelesaian (Hari)</label>
                                            <input type="number" name="resolution_time_days" value="{{ $config->resolution_time_days }}" min="1" max="365" required class="input-field" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                                        </div>
                                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                                            <button type="button" class="button button-outline" onclick="document.getElementById('edit-modal-{{ $config->id }}').style.display='none'">Batal</button>
                                            <button type="submit" class="button primary-button">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
