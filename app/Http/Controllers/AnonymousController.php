<?php

namespace App\Http\Controllers;

use App\Helpers\AnonymousTokenHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnonymousController extends Controller
{
    public function channel(Request $request)
    {
        $token = trim((string) $request->input('token'));
        $report = null;
        $messages = collect();

        if ($token !== '') {
            $report = DB::table('anonymous_report_tokens')
                ->join('reports', 'anonymous_report_tokens.report_id', '=', 'reports.id')
                ->where('anonymous_report_tokens.token_hash', AnonymousTokenHelper::hash($token))
                ->where('anonymous_report_tokens.status', true)
                ->where(function ($query) {
                    $query->whereNull('anonymous_report_tokens.expires_at')
                        ->orWhere('anonymous_report_tokens.expires_at', '>', now());
                })
                ->select('reports.id', 'reports.report_number')
                ->first();

            if ($report) {
                DB::table('anonymous_report_tokens')
                    ->where('report_id', $report->id)
                    ->update(['last_access_at' => now(), 'updated_at' => now()]);
                $messages = DB::table('anonymous_messages')
                    ->where('report_id', $report->id)
                    ->orderBy('created_at')
                    ->get();
            }
        }

        return view('anonymous.channel', compact('token', 'report', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:6', 'max:100'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $tokenClean = trim($data['token']);

        $report = DB::table('anonymous_report_tokens')
            ->where('token_hash', AnonymousTokenHelper::hash($tokenClean))
            ->where('status', true)
            ->first();

        abort_unless($report && (! $report->expires_at || now()->lessThan($report->expires_at)), 403, 'Token anonim tidak valid atau sudah kedaluwarsa.');

        DB::table('anonymous_messages')->insert([
            'report_id' => $report->report_id,
            'sender_type' => 'REPORTER',
            'message' => $data['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $caseNumber = DB::table('cases')->where('report_id', $report->report_id)->value('case_number');

        auditAction('SEND_ANONYMOUS_MESSAGE', 'REPORT', $report->report_id, null, null, true);

        notifyCounselor('ANONYMOUS_MESSAGE', 'Pesan anonim baru', 'Pelapor mengirim informasi tambahan pada laporan.', $caseNumber, 'HIGH');

        if ($request->input('redirect_to') === 'track') {
            return redirect()->route('reports.track', ['q' => $tokenClean])
                ->with('success', 'Pesan Anda berhasil dikirim ke Guru BK.');
        }

        return redirect()->route('anonymous.channel', ['token' => $tokenClean])
            ->with('success', 'Pesan anonim berhasil dikirim.');
    }
}
