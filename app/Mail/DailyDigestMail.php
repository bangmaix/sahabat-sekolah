<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  array{
     *     school_name: string,
     *     school_npsn: string,
     *     date: string,
     *     pending_cases: array<int, array<string, mixed>>,
     *     overdue_cases: array<int, array<string, mixed>>,
     *     in_handling_count: int,
     *     dashboard_url: string
     * }  $digestData
     */
    public function __construct(
        public array $digestData,
        public string $recipientName = 'Bapak/Ibu Pendidik'
    ) {}

    public function envelope(): Envelope
    {
        $overdueCount = count($this->digestData['overdue_cases'] ?? []);
        $prefix = $overdueCount > 0 ? "⚠️ [{$overdueCount} Kasus Overdue] " : '';

        return new Envelope(
            subject: "{$prefix}[Sahabat Sekolah] Ringkasan Kasus Harian - {$this->digestData['school_name']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_digest',
        );
    }
}
