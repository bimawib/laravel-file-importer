<?php

namespace App\Jobs;

use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckImportCompletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inProgressJobs = ImportJob::where('status', 'in_progress')->get();

        foreach ($inProgressJobs as $job) {
            $total = $job->total;
            $done = $job->success + $job->failed;

            if ($done === 0) {
                continue;
            }

            if ($done >= $total) {
                $newStatus = $job->failed > 0 && $job->success === 0
                    ? 'failed'
                    : 'completed';

                $job->update([
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);

                Log::info("Import job {$job->id} marked as {$newStatus}");
            }
        }
    }
}
