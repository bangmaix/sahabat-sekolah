@extends('layouts.app', [
    'title' => 'Pengguna | Sahabat Sekolah',
    'activeNav' => 'admin-users',
    'eyebrow' => 'USER MANAGEMENT',
    'pageTitle' => 'Manajemen Pengguna',
    'pageSubtitle' => 'Kelola akun, role, dan penugasan sekolah seluruh pengguna platform.'
])

@section('content')

{{-- ======================== TOP BAR ======================== --}}
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8ba0b3]"></i>
            <input id="user-search" type="text" placeholder="Cari nama atau email..."
                class="pl-9 pr-4 py-2 border border-[#dfe8f1] rounded-lg text-[13px] bg-white text-[#315675] focus:outline-none focus:border-[#4d9ae4] w-64">
        </div>
        <select id="role-filter" class="border border-[#dfe8f1] rounded-lg text-[13px] bg-white text-[#315675] px-3 py-2 focus:outline-none focus:border-[#4d9ae4]">
            <option value="">Semua Role</option>
            <option value="ADMIN">Administrator</option>
            <option value="CITY_ADMIN">Instansi Kota: Disdik & UPTD PPA (City Admin)</option>
            <option value="PRINCIPAL">Kepala Sekolah</option>
            <option value="COUNSELOR">Guru BK</option>
        </select>
    </div>
    <button id="btn-add-user"
        class="flex items-center gap-2 bg-[#1675e8] text-white text-[13px] font-bold px-4 py-2 rounded-lg shadow-[0_6px_15px_rgba(24,120,227,.2)] hover:bg-[#1262c8] transition-colors">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah Pengguna
    </button>
</div>

{{-- ======================== TABLE ======================== --}}
<div class="bg-white border border-[#e8eef5] rounded-xl overflow-hidden">
    <table class="w-full text-[13px]" id="users-table">
        <thead>
            <tr class="bg-[#f8fafc]">
                <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-5 py-3">PENGGUNA</th>
                <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">ROLE</th>
                <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">SEKOLAH</th>
                <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">STATUS</th>
                <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">BERGABUNG</th>
                <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">AKSI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr class="border-t border-[#edf2f7] hover:bg-[#fafcff] transition-colors user-row"
                data-name="{{ strtolower($user->name) }}"
                data-email="{{ strtolower($user->email) }}"
                data-role="{{ $user->role }}">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#dbeafe] text-[#1d4ed8] flex items-center justify-center text-[12px] font-bold flex-none">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <strong class="block text-[#1d4c7e]">{{ $user->name }}</strong>
                            <small class="text-[#a1adbb] text-[11px]">{{ $user->email }}</small>
                        </div>
                    </div>
                </td>
                <td class="px-3 py-3">
                    @php
                        $isPpa = str_contains($user->school_name ?? '', 'PPA') || str_contains($user->school_name ?? '', 'DP3APPKB');
                        $roleColors = [
                            'ADMIN' => 'bg-purple-100 text-purple-700',
                            'CITY_ADMIN' => $isPpa ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700',
                            'PRINCIPAL' => 'bg-blue-100 text-blue-700',
                            'COUNSELOR' => 'bg-emerald-100 text-emerald-700',
                        ];
                        $roleLabels = [
                            'ADMIN' => 'Administrator',
                            'CITY_ADMIN' => $isPpa ? 'UPTD PPA (City)' : 'Disdik (City)',
                            'PRINCIPAL' => 'Kepala Sekolah',
                            'COUNSELOR' => 'Guru BK',
                        ];
                    @endphp
                    <span class="px-2 py-1 rounded text-[11px] font-semibold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $roleLabels[$user->role] ?? $user->role }}
                    </span>
                </td>
                <td class="px-3 py-3 text-[#5f7890]">
                    <span class="max-w-[170px] block truncate" title="{{ $user->school_name }}">
                        {{ $user->school_name !== '-' ? $user->school_name : '—' }}
                    </span>
                </td>
                <td class="px-3 py-3">
                    @php $status = $user->status ?? 'ACTIVE'; @endphp
                    <span class="flex items-center gap-1.5 text-[11px] font-semibold {{ $status === 'ACTIVE' ? 'text-emerald-600' : 'text-gray-400' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                        {{ $status === 'ACTIVE' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-3 py-3 text-[#9aaaba] text-[11px] whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($user->created_at)->locale('id')->isoFormat('D MMM Y') }}
                </td>
                <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                        <button
                            class="btn-edit flex items-center gap-1 bg-[#edf5ff] text-[#1675e8] text-[11px] font-semibold px-2.5 py-1.5 rounded-lg hover:bg-[#dbeafe] transition-colors"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-role="{{ $user->role }}"
                            data-school="{{ $user->school_npsn }}"
                            data-agency="{{ $user->school_name }}"
                            data-status="{{ $user->status ?? 'ACTIVE' }}">
                            <i data-lucide="pencil" class="w-3 h-3"></i> Edit
                        </button>
                        <button
                            class="btn-reset-password flex items-center gap-1 bg-[#fff8eb] text-[#d97706] text-[11px] font-semibold px-2.5 py-1.5 rounded-lg hover:bg-[#fef3c7] transition-colors"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            title="Reset Password Pengguna">
                            <i data-lucide="key-round" class="w-3 h-3"></i> Reset
                        </button>
                        <button
                            class="btn-delete flex items-center gap-1 bg-[#fff0f0] text-[#e6636a] text-[11px] font-semibold px-2.5 py-1.5 rounded-lg hover:bg-[#ffe2e3] transition-colors"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}">
                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div id="no-results" class="hidden text-center text-[#9aaaba] py-10 text-[13px]">
        <i data-lucide="search-x" class="w-7 h-7 mx-auto mb-2 text-[#c0d0de]"></i>
        <p>Tidak ada pengguna yang cocok.</p>
    </div>
</div>

{{-- ======================== MODAL: ADD USER ======================== --}}
<div id="modal-add" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-add-title">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="modal-add-backdrop"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 animate-modal">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 id="modal-add-title" class="font-extrabold text-[19px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Tambah Pengguna</h2>
                <p class="text-[12px] text-[#8494a9] mt-1">Kata sandi acak 8 karakter akan dibuat dan otomatis dikirimkan ke email pengguna.</p>
            </div>
            <button id="modal-add-close" class="w-8 h-8 rounded-full bg-[#f3f7fb] text-[#7390b4] flex items-center justify-center hover:bg-[#e8eef5] transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form id="form-add-user" method="POST" action="{{ route('admin.users.store') }}" class="grid gap-3">
            @csrf
            <div>
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input name="name" required placeholder="Contoh: Budi Santoso" value="{{ old('name') }}"
                    class="w-full border @error('name') border-red-400 bg-red-50/20 @else border-[#dfe8f1] bg-[#fbfdff] @enderror rounded-lg px-3 py-2.5 text-[13px] text-[#315675] focus:outline-none focus:border-[#4d9ae4] focus:ring-2 focus:ring-[#e7f3ff]">
                @error('name')
                    <p class="text-[12px] text-red-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="email@contoh.com" value="{{ old('email') }}"
                    class="w-full border @error('email') border-red-400 bg-red-50/20 @else border-[#dfe8f1] bg-[#fbfdff] @enderror rounded-lg px-3 py-2.5 text-[13px] text-[#315675] focus:outline-none focus:border-[#4d9ae4] focus:ring-2 focus:ring-[#e7f3ff]">
                @error('email')
                    <p class="text-[12px] text-red-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select id="add-role" name="role" class="w-full border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-[#315675] bg-[#fbfdff] focus:outline-none focus:border-[#4d9ae4]">
                        <option value="COUNSELOR" {{ old('role') === 'COUNSELOR' ? 'selected' : '' }}>Guru BK</option>
                        <option value="PRINCIPAL" {{ old('role') === 'PRINCIPAL' ? 'selected' : '' }}>Kepala Sekolah</option>
                        <option value="CITY_ADMIN" {{ old('role') === 'CITY_ADMIN' ? 'selected' : '' }}>Instansi Kota / Satgas PPKSP (City Admin)</option>
                        <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>
                <div id="add-school-wrapper">
                    <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Sekolah</label>
                    <div class="tw-select-container relative w-full" data-name="school_npsn">
                        <select name="school_npsn" class="hidden">
                            <option value="">Tanpa sekolah</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->npsn }}" {{ old('school_npsn') === $school->npsn ? 'selected' : '' }}>{{ $school->nama_sekolah }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="tw-select-trigger w-full flex items-center justify-between bg-[#fbfdff] border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-left focus:outline-none focus:border-[#4d9ae4] focus:ring-2 focus:ring-[#e7f3ff] transition-colors">
                            <span class="tw-select-text text-[#315675] truncate">Pilih sekolah...</span>
                            <svg class="w-3 h-3 text-[#7390b4] pointer-events-none flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="tw-select-dropdown absolute z-50 w-full mt-1 bg-white border border-[#dfe8f1] rounded-lg shadow-lg hidden opacity-0 transition-opacity duration-200">
                            <div class="p-2 border-b border-[#edf2f7]">
                                <div class="relative">
                                    <svg class="w-3 h-3 text-[#7390b4] absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input type="text" class="tw-select-search w-full bg-[#f8fafc] border-none text-[13px] rounded pl-7 pr-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#4d9ae4]" placeholder="Cari sekolah...">
                                </div>
                            </div>
                            <ul class="tw-select-list max-h-48 overflow-y-auto custom-scrollbar p-1" role="listbox"></ul>
                            <div class="tw-select-empty hidden p-3 text-center text-[12px] text-[#7390b4]">Pencarian tidak ditemukan.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="add-city-agency-wrapper" class="hidden">
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Instansi Tingkat Kota <span class="text-red-500">*</span></label>
                <select name="city_agency" id="add-city-agency" class="w-full border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-[#315675] bg-[#fbfdff] focus:outline-none focus:border-[#4d9ae4]">
                    <option value="Dinas Pendidikan & Kebudayaan Kota Bukittinggi">Dinas Pendidikan & Kebudayaan Kota Bukittinggi</option>
                    <option value="UPTD PPA - DP3APPKB Kota Bukittinggi">UPTD PPA (DP3APPKB Kota Bukittinggi)</option>
                    <option value="Satgas PPKSP Kota Bukittinggi">Satgas PPKSP Kota Bukittinggi</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2 mt-1">
                <button type="button" id="modal-add-cancel"
                    class="px-4 py-2 text-[13px] font-semibold text-[#5c7b98] bg-[#f3f8fd] border border-[#dfebf5] rounded-lg hover:bg-[#e8f0f8] transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn-submit-add-user"
                    class="flex items-center gap-2 px-4 py-2 text-[13px] font-bold bg-[#1675e8] text-white rounded-lg shadow-[0_4px_12px_rgba(24,120,227,.25)] hover:bg-[#1262c8] transition-colors">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Buat Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: EDIT USER ======================== --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="modal-edit-backdrop"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 animate-modal">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 id="modal-edit-title" class="font-extrabold text-[19px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Edit Pengguna</h2>
                <p id="edit-subtitle" class="text-[12px] text-[#8494a9] mt-1">Ubah detail akun pengguna</p>
            </div>
            <button id="modal-edit-close" class="w-8 h-8 rounded-full bg-[#f3f7fb] text-[#7390b4] flex items-center justify-center hover:bg-[#e8eef5] transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form id="edit-form" method="POST" class="grid gap-3">
            @csrf
            <div>
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input id="edit-name" name="name" required
                    class="w-full border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-[#315675] bg-[#fbfdff] focus:outline-none focus:border-[#4d9ae4] focus:ring-2 focus:ring-[#e7f3ff]">
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Email</label>
                <input id="edit-email" type="email" disabled
                    class="w-full border border-[#e8eef5] rounded-lg px-3 py-2.5 text-[13px] text-[#9aa9ba] bg-[#f8fafc] cursor-not-allowed">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select id="edit-role" name="role" class="w-full border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-[#315675] bg-[#fbfdff] focus:outline-none focus:border-[#4d9ae4]">
                        <option value="COUNSELOR">Guru BK</option>
                        <option value="PRINCIPAL">Kepala Sekolah</option>
                        <option value="CITY_ADMIN">Instansi Kota / Satgas PPKSP (City Admin)</option>
                        <option value="ADMIN">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select id="edit-status" name="status" class="w-full border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-[#315675] bg-[#fbfdff] focus:outline-none focus:border-[#4d9ae4]">
                        <option value="ACTIVE">Aktif</option>
                        <option value="INACTIVE">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div id="edit-city-agency-wrapper" class="hidden">
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Instansi Tingkat Kota <span class="text-red-500">*</span></label>
                <select name="city_agency" id="edit-city-agency" class="w-full border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-[#315675] bg-[#fbfdff] focus:outline-none focus:border-[#4d9ae4]">
                    <option value="Dinas Pendidikan & Kebudayaan Kota Bukittinggi">Dinas Pendidikan & Kebudayaan Kota Bukittinggi</option>
                    <option value="UPTD PPA - DP3APPKB Kota Bukittinggi">UPTD PPA (DP3APPKB Kota Bukittinggi)</option>
                    <option value="Satgas PPKSP Kota Bukittinggi">Satgas PPKSP Kota Bukittinggi</option>
                </select>
            </div>
            <div id="edit-school-wrapper">
                <label class="block text-[12px] font-semibold text-[#496985] mb-1.5">Sekolah</label>
                <div class="tw-select-container relative w-full" data-name="school_npsn">
                    <select id="edit-school" name="school_npsn" class="hidden">
                        <option value="">Tanpa sekolah (Platform)</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->npsn }}">{{ $school->nama_sekolah }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="tw-select-trigger w-full flex items-center justify-between bg-[#fbfdff] border border-[#dfe8f1] rounded-lg px-3 py-2.5 text-[13px] text-left focus:outline-none focus:border-[#4d9ae4] focus:ring-2 focus:ring-[#e7f3ff] transition-colors">
                        <span class="tw-select-text text-[#315675] truncate">Pilih sekolah...</span>
                        <svg class="w-3 h-3 text-[#7390b4] pointer-events-none flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="tw-select-dropdown absolute z-50 w-full mt-1 bg-white border border-[#dfe8f1] rounded-lg shadow-lg hidden opacity-0 transition-opacity duration-200">
                        <div class="p-2 border-b border-[#edf2f7]">
                            <div class="relative">
                                <svg class="w-3 h-3 text-[#7390b4] absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input type="text" class="tw-select-search w-full bg-[#f8fafc] border-none text-[13px] rounded pl-7 pr-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#4d9ae4]" placeholder="Cari sekolah...">
                            </div>
                        </div>
                        <ul class="tw-select-list max-h-48 overflow-y-auto custom-scrollbar p-1" role="listbox"></ul>
                        <div class="tw-select-empty hidden p-3 text-center text-[12px] text-[#7390b4]">Pencarian tidak ditemukan.</div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 mt-1">
                <button type="button" id="modal-edit-cancel"
                    class="px-4 py-2 text-[13px] font-semibold text-[#5c7b98] bg-[#f3f8fd] border border-[#dfebf5] rounded-lg hover:bg-[#e8f0f8] transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 text-[13px] font-bold bg-[#1675e8] text-white rounded-lg shadow-[0_4px_12px_rgba(24,120,227,.25)] hover:bg-[#1262c8] transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: DELETE CONFIRM ======================== --}}
<div id="modal-delete" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="modal-delete-backdrop"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 animate-modal text-center">
        <div class="w-14 h-14 rounded-full bg-[#ffe2e3] flex items-center justify-center mx-auto mb-4">
            <i data-lucide="trash-2" class="w-7 h-7 text-[#e6636a]"></i>
        </div>
        <h2 class="font-extrabold text-[19px] text-[#193d6c] mb-2" style="font-family:'Plus Jakarta Sans'">Hapus Pengguna?</h2>
        <p class="text-[13px] text-[#8494a9] mb-5">Akun <strong id="delete-name" class="text-[#315675]"></strong> akan dihapus secara permanen dan tidak dapat dipulihkan.</p>
        <form id="delete-form" method="POST">
            @csrf
            <div class="flex justify-center gap-3">
                <button type="button" id="modal-delete-cancel"
                    class="px-5 py-2 text-[13px] font-semibold text-[#5c7b98] bg-[#f3f8fd] border border-[#dfebf5] rounded-lg hover:bg-[#e8f0f8] transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex items-center gap-2 px-5 py-2 text-[13px] font-bold bg-[#e6636a] text-white rounded-lg hover:bg-[#d4565c] transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ======================== MODAL: RESET PASSWORD ======================== --}}
<div id="modal-reset-password" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-reset-title">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="modal-reset-backdrop"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 animate-modal text-center">
        <div class="w-14 h-14 rounded-full bg-[#fef3c7] text-[#d97706] flex items-center justify-center mx-auto mb-4">
            <i data-lucide="key-round" class="w-7 h-7"></i>
        </div>
        <h2 id="modal-reset-title" class="font-extrabold text-[19px] text-[#193d6c] mb-2" style="font-family:'Plus Jakarta Sans'">Reset Kata Sandi?</h2>
        <p class="text-[13px] text-[#8494a9] mb-4">
            Kata sandi untuk <strong id="reset-user-name" class="text-[#315675]"></strong> akan direset menjadi <strong class="text-[#193d6c]">kata sandi acak 8 karakter</strong> baru dan langsung dikirimkan ke email <span id="reset-user-email" class="font-mono text-[#1675e8]"></span>.
        </p>
        <form id="reset-password-form" method="POST">
            @csrf
            <div class="flex justify-center gap-3">
                <button type="button" id="modal-reset-cancel"
                    class="px-5 py-2 text-[13px] font-semibold text-[#5c7b98] bg-[#f3f8fd] border border-[#dfebf5] rounded-lg hover:bg-[#e8f0f8] transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex items-center gap-2 px-5 py-2 text-[13px] font-bold bg-[#d97706] text-white rounded-lg hover:bg-[#b45309] transition-colors shadow-sm">
                    <i data-lucide="key-round" class="w-4 h-4"></i> Ya, Reset Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.animate-modal { animation: modal-in 0.2s ease; }
@keyframes modal-in { from { opacity:0; transform:scale(.96) translateY(8px); } to { opacity:1; transform:none; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- helpers ----
    function openModal(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
    function closeModal(el) { el.classList.remove('flex'); el.classList.add('hidden'); }

    // ---- Search & Filter ----
    const searchInput = document.getElementById('user-search');
    const roleFilter = document.getElementById('role-filter');
    const rows = document.querySelectorAll('.user-row');
    const noResults = document.getElementById('no-results');

    function filterTable() {
        const query = searchInput.value.toLowerCase();
        const role = roleFilter.value;
        let visible = 0;
        rows.forEach(row => {
            const matchText = row.dataset.name.includes(query) || row.dataset.email.includes(query);
            const matchRole = !role || row.dataset.role === role;
            if (matchText && matchRole) { row.style.display = ''; visible++; }
            else { row.style.display = 'none'; }
        });
        noResults.classList.toggle('hidden', visible > 0);
    }
    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);

    // ---- Add Modal ----
    const modalAdd = document.getElementById('modal-add');
    const addRoleSelect = document.getElementById('add-role');
    function toggleAddRole() {
        const role = addRoleSelect?.value;
        const agencyWrapper = document.getElementById('add-city-agency-wrapper');
        const schoolWrapper = document.getElementById('add-school-wrapper');
        if (role === 'CITY_ADMIN') {
            agencyWrapper?.classList.remove('hidden');
            schoolWrapper?.classList.add('hidden');
        } else if (role === 'ADMIN') {
            agencyWrapper?.classList.add('hidden');
            schoolWrapper?.classList.add('hidden');
        } else {
            agencyWrapper?.classList.add('hidden');
            schoolWrapper?.classList.remove('hidden');
        }
    }
    addRoleSelect?.addEventListener('change', toggleAddRole);

    document.getElementById('btn-add-user').addEventListener('click', () => {
        toggleAddRole();
        openModal(modalAdd);
    });
    document.getElementById('modal-add-close').addEventListener('click', () => closeModal(modalAdd));
    document.getElementById('modal-add-cancel').addEventListener('click', () => closeModal(modalAdd));
    document.getElementById('modal-add-backdrop').addEventListener('click', () => closeModal(modalAdd));

    @if ($errors->any())
        toggleAddRole();
        openModal(modalAdd);
    @endif

    const formAdd = document.getElementById('form-add-user');
    if (formAdd) {
        formAdd.addEventListener('submit', function (e) {
            const btn = document.getElementById('btn-submit-add-user');
            if (btn && btn.disabled) {
                e.preventDefault();
                return false;
            }
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span> Menyimpan...';
            }
        });
    }

    // ---- Edit Modal ----
    const modalEdit = document.getElementById('modal-edit');
    const editRoleSelect = document.getElementById('edit-role');
    function toggleEditRole() {
        const role = editRoleSelect?.value;
        const agencyWrapper = document.getElementById('edit-city-agency-wrapper');
        const schoolWrapper = document.getElementById('edit-school-wrapper');
        if (role === 'CITY_ADMIN') {
            agencyWrapper?.classList.remove('hidden');
            schoolWrapper?.classList.add('hidden');
        } else if (role === 'ADMIN') {
            agencyWrapper?.classList.add('hidden');
            schoolWrapper?.classList.add('hidden');
        } else {
            agencyWrapper?.classList.add('hidden');
            schoolWrapper?.classList.remove('hidden');
        }
    }
    editRoleSelect?.addEventListener('change', toggleEditRole);

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('edit-form').action = `/admin/users/${d.id}`;
            document.getElementById('edit-name').value = d.name;
            document.getElementById('edit-email').value = d.email;
            document.getElementById('edit-role').value = d.role;
            document.getElementById('edit-status').value = d.status;

            if (d.role === 'CITY_ADMIN' && d.agency) {
                const agencySelect = document.getElementById('edit-city-agency');
                if (agencySelect) {
                    agencySelect.value = d.agency;
                }
            }
            toggleEditRole();
            
            const editSchool = document.getElementById('edit-school');
            editSchool.value = d.school || '';
            editSchool.dispatchEvent(new Event('change'));

            document.getElementById('edit-subtitle').textContent = d.email;
            openModal(modalEdit);
        });
    });
    document.getElementById('modal-edit-close').addEventListener('click', () => closeModal(modalEdit));
    document.getElementById('modal-edit-cancel').addEventListener('click', () => closeModal(modalEdit));
    document.getElementById('modal-edit-backdrop').addEventListener('click', () => closeModal(modalEdit));

    // ---- Delete Modal ----
    const modalDelete = document.getElementById('modal-delete');
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('delete-name').textContent = btn.dataset.name;
            document.getElementById('delete-form').action = `/admin/users/${btn.dataset.id}/delete`;
            openModal(modalDelete);
        });
    });
    document.getElementById('modal-delete-cancel').addEventListener('click', () => closeModal(modalDelete));
    document.getElementById('modal-delete-backdrop').addEventListener('click', () => closeModal(modalDelete));

    // ---- Reset Password Modal ----
    const modalReset = document.getElementById('modal-reset-password');
    const resetForm = document.getElementById('reset-password-form');
    const resetName = document.getElementById('reset-user-name');
    const resetEmail = document.getElementById('reset-user-email');

    document.querySelectorAll('.btn-reset-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const email = btn.dataset.email;

            resetName.textContent = name;
            resetEmail.textContent = email;
            resetForm.action = `/admin/users/${id}/reset-password`;
            openModal(modalReset);
        });
    });
    document.getElementById('modal-reset-cancel').addEventListener('click', () => closeModal(modalReset));
    document.getElementById('modal-reset-backdrop').addEventListener('click', () => closeModal(modalReset));

    // ---- Close on ESC ----
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal(modalAdd);
            closeModal(modalEdit);
            closeModal(modalDelete);
            closeModal(modalReset);
        }
    });
});
</script>
@endsection
