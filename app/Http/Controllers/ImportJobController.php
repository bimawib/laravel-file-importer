<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ImportJob;
use App\Services\ImportJobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportJobController extends Controller
{
    public function __construct(private ImportJobService $importJobService) {}

    public function listImportJobs(Request $request) {
        try {
            $result = $this->importJobService->listImportJobs($request);
            return ApiResponse::success(
                data: $result['data'],
                message: 'Import job list retrieved successfully',
                pagination: $result['pagination']
            );
        } catch (\Throwable $th) {
            Log::error('Failed to fetch import jobs', ['error' => $th->getMessage()]);
            return ApiResponse::error('Failed to fetch import jobs', 500, $th->getMessage());
        }
    }

    public function getImportJobStatus(ImportJob $importJob) {
        try {
            $result = $this->importJobService->getImportJobStatus($importJob);
            return ApiResponse::success(
                data: $result,
                message: 'Import job status retrieved successfully'
            );
        } catch (\Throwable $th) {
            Log::error('Failed to fetch import job status', ['error' => $th->getMessage()]);
            return ApiResponse::error('Failed to fetch import job status', 404, $th->getMessage());
        }
    }
}
