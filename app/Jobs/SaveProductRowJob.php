<?php

namespace App\Jobs;

use App\Models\ImportError;
use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class SaveProductRowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

protected string $jobId;
    protected array $row;

    public function __construct(string $jobId, array $row)
    {
        $this->jobId = $jobId;
        $this->row = $row;
    }

    public function handle(): void
    {
        $importJob = ImportJob::find($this->jobId);

        if (!$importJob) {
            return;
        }

        try {
            $validator = Validator::make([
                'name' => $this->row[0] ?? null,
                'sku' => $this->row[1] ?? null,
                'price' => $this->row[2] ?? null,
                'stock' => $this->row[3] ?? null
            ], [
                'name' => ['required', 'string', 'max:255'],
                'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0']
            ]);

            if ($validator->fails()) {
                $this->logError($importJob, $validator->errors()->first(), $this->row);
                $importJob->increment('failed');
                return;
            }

            Product::create([
                'name' => $this->row[0],
                'sku' => $this->row[1],
                'price' => $this->row[2],
                'stock' => $this->row[3],
                'entity_type' => 'products'
            ]);

            $importJob->increment('success');
        } catch (Exception $e) {
            $this->logError($importJob, $e->getMessage(), $this->row);
            $importJob->increment('failed');
        }
    }

    protected function logError(ImportJob $importJob, string $message, array $row) {
        Log::error("Saving data from import job {$this->jobId} failed: " . $message);
        ImportError::create([
            'import_job_id' => $importJob->id,
            'entity_type' => 'products',
            'raw_data' => json_encode($row),
            'error_message' => $message
        ]);
    }
}
