<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuspectedBreachAdminAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal pengiriman email jika terjadi kendala jaringan/SMTP.
     */
    public int $tries = 5;

    /**
     * Waktu tunggu (detik) sebelum pengiriman ulang jika terjadi kegagalan sementara.
     */
    public int $backoff = 60;

    /**
     * @param  array{
     *     victim_name: string,
     *     victim_email: string,
     *     victim_role: string,
     *     victim_school: ?string,
     *     reported_at: string,
     *     ip_address: string,
     *     user_agent: ?string,
     *     admin_users_url: string
     * }  $incidentData
     */
    public function __construct(
        public array $incidentData,
        public string $recipientName = 'Administrator'
    ) {}

    public function envelope(): Envelope
    {
        $victimEmail = $this->incidentData['victim_email'] ?? 'Pengguna';

        return new Envelope(
            subject: "[PERINGATAN KEAMANAN] Dugaan Akun Diretas: {$victimEmail}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suspected_breach_admin_alert',
        );
    }

    /**
     * Catat log jika email gagal dikirim setelah seluruh percobaan habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Pengiriman email peringatan keamanan admin gagal setelah mencapai batas maksimal percobaan.', [
            'recipient' => $this->recipientName,
            'victim_email' => $this->incidentData['victim_email'] ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}
