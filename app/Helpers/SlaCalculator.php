<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class SlaCalculator
{
    public const WORK_START_HOUR = 7;

    public const WORK_START_MINUTE = 30;

    public const WORK_END_HOUR = 15;

    public const WORK_END_MINUTE = 30;

    /**
     * Menghitung batas waktu (deadline) SLA.
     * Kasus CRITICAL dihitung 24/7 tanpa henti (darurat darurat/ancaman nyawa).
     * Kasus lain (HIGH, MEDIUM, LOW) dihitung mengikuti jam operasional sekolah Kota Bukittinggi:
     * Senin - Jumat, 07:30 - 15:30 WIB.
     */
    public static function calculateDeadline(Carbon $from, int $hours, string $riskLevel = 'MEDIUM'): Carbon
    {
        if (strtoupper($riskLevel) === 'CRITICAL') {
            return $from->copy()->addHours($hours);
        }

        $originalTz = $from->getTimezone();
        $current = $from->copy()->setTimezone('Asia/Jakarta');

        $remainingMinutes = $hours * 60;

        while ($remainingMinutes > 0) {
            // Jika akhir pekan (Sabtu = 6, Minggu = 7 / 0)
            if ($current->isWeekend()) {
                $current->next(CarbonInterface::MONDAY)->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE);

                continue;
            }

            $dayStart = $current->copy()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE);
            $dayEnd = $current->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE);

            if ($current->lt($dayStart)) {
                $current = $dayStart->copy();
            } elseif ($current->gte($dayEnd)) {
                $current->addDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE);

                continue;
            }

            // Hitung menit yang tersedia hari ini hingga jam 15:30
            $availableMinutesToday = $current->diffInMinutes($dayEnd, false);

            if ($availableMinutesToday <= 0) {
                $current->addDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE);

                continue;
            }

            if ($remainingMinutes <= $availableMinutesToday) {
                $current->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                $remainingMinutes -= $availableMinutesToday;
                $current->addDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE);
            }
        }

        return $current->setTimezone($originalTz);
    }
}
