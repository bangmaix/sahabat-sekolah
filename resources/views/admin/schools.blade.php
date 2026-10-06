@extends('layouts.app', [
    'title' => 'Sekolah | Sahabat Sekolah',
    'activeNav' => 'admin-schools',
    'eyebrow' => 'SCHOOL MANAGEMENT',
    'pageTitle' => 'Manajemen Data Sekolah',
    'pageSubtitle' => 'Daftar sekolah yang terintegrasi dari API.'
])

@section('content')
<section class="panel admin-table-panel">
    <table class="admin-crud-table">
        <thead>
            <tr>
                <th>NPSN</th>
                <th>Nama Sekolah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schools as $school)
                <tr>
                    <td>{{ $school->npsn }}</td>
                    <td>{{ $school->nama_sekolah }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
