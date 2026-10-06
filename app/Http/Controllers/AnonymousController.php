<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnonymousController extends Controller
{
    public function channel(Request $request)
    {
        $token = $request->input('token');
        $report = null;
        $messages = collect();

        if ($token) {
            $report = DB::table('anonymous_report_tokens')
                ->join('reports', 'anonymous_report_tokens.report_id', '=', 'reports.id')
                ->where('anonymous_report_tokens.token_hash', hash('sha256', strtoupper($token)))
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
            'token' => ['required', 'string', 'size:12'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $report = DB::table('anonymous_report_tokens')
            ->where('token_hash', hash('sha256', strtoupper($data['token'])))
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

        notifyCounselor('ANONYMOUS_MESSAGE', 'Pesan anonim baru', 'Pelapor mengirim informasi tambahan pada laporan.', null, 'HIGH');

        return redirect()->route('anonymous.channel', ['token' => strtoupper($data['token'])])
            ->with('success', 'Pesan anonim berhasil dikirim.');
    }
}
