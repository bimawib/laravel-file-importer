<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ImportErrorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportErrorController extends Controller
{
    public function __construct(private ImportErrorService $importErrorService) {}

    public function listImportErrors(Request $request) {
        try {
            $result = $this->importErrorService->listImportErrors($request);
            return ApiResponse::success(
                data: $result['data'],
                message: 'Import error list retrieved successfully',
                pagination: $result['pagination']
            );
        } catch (\Throwable $th) {
            Log::error('Failed to fetch import errors', ['error' => $th->getMessage()]);
            return ApiResponse::error('Failed to fetch import errors', 500, $th->getMessage());
        }
    }
}
