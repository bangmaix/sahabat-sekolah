<?php

namespace App\Console\Commands;

use App\Helpers\AConnect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSchoolNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sahabat:sync-school-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync school names from API to existing users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi nama sekolah dari API...');

        $schoolsMap = (new AConnect)->getSekolahMap();
        $users = DB::table('users')->whereNotNull('school_npsn')->get();

        $updatedCount = 0;
        foreach ($users as $user) {
            if (isset($schoolsMap[$user->school_npsn])) {
                $schoolName = $schoolsMap[$user->school_npsn];
                DB::table('users')->where('id', $user->id)->update(['school_name' => $schoolName]);
                $this->line("Synced User ID {$user->id} -> {$schoolName}");
                $updatedCount++;
            }
        }

        $this->info("Berhasil melakukan sinkronisasi untuk {$updatedCount} akun.");

        return self::SUCCESS;
    }
}
