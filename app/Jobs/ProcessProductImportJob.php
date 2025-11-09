<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Jobs\SaveProductRowJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;
use Exception;
use SplFileObject;
use Illuminate\Support\Facades\Log;

class ProcessProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $importJob = ImportJob::findOrFail($this->jobId);
        $importJob->update(['status' => 'in_progress']);

        try {
            $path = $importJob->filename;
            $fullPath = Storage::path($path);

            $fileObject = new SplFileObject($fullPath, 'r');
            $csv = Reader::createFromFileObject($fileObject);

            $records = (new Statement())->process($csv);
            $rows = iterator_to_array($records);
            array_shift($rows);
            $totalRows = count($rows);

            $importJob->update(['total' => $totalRows]);

            if ($totalRows > 0) {
                foreach ($rows as $row) {
                    SaveProductRowJob::dispatch($this->jobId, $row);
                }
            } else {
                $importJob->update(['status' => 'failed']);
            }
        } catch (Exception $e) {
            $importJob->update(['status' => 'failed']);
            Log::error("Import job {$this->jobId} failed: " . $e->getMessage());
        }
    }
}
