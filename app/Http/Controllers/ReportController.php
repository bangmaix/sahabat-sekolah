<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function create()
    {
        $schools = new AConnect;
        $data = collect($schools->getDataSekolah()['data'])
            ->map(fn ($item) => (object) $item)
            ->sortBy('nama_sekolah')
            ->values();

        return view('reports.create', [
            'schools' => $data,
            'categories' => DB::table('bullying_categories')->where('status', true)->orderBy('name')->get()->map(function ($category) {
                $category->subcategories = DB::table('bullying_subcategories')->where('category_id', $category->id)->where('status', true)->orderBy('name')->get();

                return $category;
            }),
        ]);
    }

    public function inbox(Request $request)
    {
        $query = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', session('school_npsn'))
            ->select('cases.*', 'reports.description', 'reports.reporter_role', 'reports.identity_mode', 'case_slas.status as sla_status', 'case_slas.resolution_status');

        if ($status = $request->input('status')) {
            if ($status !== 'ALL') {
                $query->where('cases.status', $status);
            }
        } else {
            $query->where('cases.status', '!=', 'CLOSED');
        }

        if ($risk = $request->input('risk_level')) {
            $query->where('cases.risk_level', $risk);
        }

        if ($sla = $request->input('sla_status')) {
            if ($sla === 'COMPLETED') {
                $query->where(function ($q) {
                    $q->whereIn('cases.status', ['RESOLVED', 'CLOSED'])
                      ->orWhere('case_slas.resolution_status', 'COMPLETED');
                });
            } else {
                $query->where('cases.status', '!=', 'PENDING_RESPONSE')
                      ->where('case_slas.resolution_status', $sla);
            }
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('cases.case_number', 'like', '%'.$search.'%')
                    ->orWhere('cases.category', 'like', '%'.$search.'%')
                    ->orWhere('reports.description', 'like', '%'.$search.'%');
            });
        }

        return view('reports.inbox', [
            'cases' => $query->orderByDesc('cases.created_at')->get(),
            'filters' => $request->only(['status', 'risk_level', 'sla_status', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_npsn' => ['required', 'string'],
            'reporter_role' => ['required', 'in:VICTIM,WITNESS,CONCERNED_PERSON'],
            'identity_mode' => ['required', 'in:IDENTIFIED,CONFIDENTIAL,ANONYMOUS'],
            'reporter_name' => ['nullable', 'string', 'max:255', 'required_unless:identity_mode,ANONYMOUS'],
            'reporter_contact' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:255', 'exists:bullying_categories,name'],
            'subcategory' => ['nullable', 'string', 'max:255', 'exists:bullying_subcategories,name'],
            'description' => ['required', 'string', 'min:20'],
        ], [
            'school_npsn.required' => 'Pilih sekolah terlebih dahulu.',
            'reporter_role.required' => 'Pilih peran pelapor (Saya korban, Saya saksi, atau Saya mengetahui kejadian).',
            'identity_mode.required' => 'Pilih mode identitas laporan.',
            'reporter_name.required_unless' => 'Nama pelapor wajib diisi jika mode identitas Rahasia atau Terbuka.',
            'category.required' => 'Pilih kategori kejadian perundungan.',
            'description.required' => 'Ceritakan kronologi kejadian perundungan.',
            'description.min' => 'Cerita kejadian minimal 20 karakter agar informasi laporan cukup jelas.',
        ]);

        $caseNumber = DB::transaction(function () use ($data) {
            $number = 'SS-'.now()->format('Y').'-'.str_pad((string) ((DB::table('reports')->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
            $now = now();
            $reportData = $data;
            unset($reportData['reporter_name'], $reportData['reporter_contact']);

            $reportId = DB::table('reports')->insertGetId([
                ...$reportData, 'report_number' => $number, 'submitted_at' => $now,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            $risk = in_array($data['category'], ['Cyberbullying', 'Fisik', 'Perundungan Bernuansa Seksual'], true) ? 'HIGH' : 'MEDIUM';

            $caseId = DB::table('cases')->insertGetId([
                'school_npsn' => $data['school_npsn'], 'report_id' => $reportId, 'case_number' => $number,
                'category' => $data['category'], 'risk_level' => $risk, 'status' => 'PENDING_RESPONSE',
                'opened_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);

            $config = DB::table('sla_configurations')->where('risk_level', $risk)->first();
            $responseHours = $config ? $config->response_time_hours : 24;
            $resolutionDays = $config ? $config->resolution_time_days : 14;

            DB::table('case_slas')->insert([
                'case_id' => $caseId, 
                'status' => 'ON_TIME', 
                'response_deadline' => $now->copy()->addHours($responseHours),
                'resolution_deadline' => null, // dihitung ketika direspon
                'resolution_status' => 'ON_TRACK',
                'created_at' => $now, 
                'updated_at' => $now,
            ]);

            if ($data['identity_mode'] !== 'ANONYMOUS') {
                DB::table('reporter_identities')->insert([
                    'report_id' => $reportId,
                    'full_name' => $data['reporter_name'] ?? null,
                    'contact' => $data['reporter_contact'] ?? null,
                    'access_level' => $data['identity_mode'] === 'IDENTIFIED' ? 'CASE_FULL' : 'CASE_RESTRICTED',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            notifyCounselor('NEW_REPORT', 'Laporan baru masuk', $number.' menunggu respons awal dalam 1 × 24 jam.', $number, 'HIGH');

            $anonymousToken = null;
            if ($data['identity_mode'] === 'ANONYMOUS') {
                $anonymousToken = Str::upper(Str::random(12));
                DB::table('anonymous_report_tokens')->insert([
                    'report_id' => $reportId, 'token_hash' => hash('sha256', $anonymousToken),
                    'expires_at' => $now->copy()->addMonths(6), 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            return ['number' => $number, 'token' => $anonymousToken];
        });

        if ($caseNumber['token']) {
            return redirect()->route('reports.receipt')->with(['report_number' => $caseNumber['number'], 'anonymous_token' => $caseNumber['token']]);
        }

        return redirect()->route('cases.show', $caseNumber['number'])
            ->with('success', 'Laporan berhasil dibuat dan masuk ke antrean Guru BK.');
    }

    public function receipt()
    {
        abort_unless(session()->has('anonymous_token'), 404);

        return view('reports.receipt', [
            'reportNumber' => session('report_number'),
            'anonymousToken' => session('anonymous_token'),
        ]);
    }
}
