<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\ProductService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}
    
    public function importProducts(Request $request) {
        try {
            $result = $this->productService->importProducts($request);

            return ApiResponse::success(
                data: $result['data'],
                message: $result['message']
            );
        } catch (\Throwable $th) {
            $errorMsg = 'Failed to import products from a csv file';
            Log::error($errorMsg, ['error' => $th->getMessage()]);

            return ApiResponse::error($errorMsg, 400, $th->getMessage());
        }
    }
    
    public function listProducts(Request $request) {
        try {
            $result = $this->productService->listProducts($request);
            return ApiResponse::success(
                data: $result['data'],
                message: 'Product list retrieved successfully',
                pagination: $result['pagination']
            );
        } catch (\Throwable $th) {
            Log::error('Failed to fetch product list', ['error' => $th->getMessage()]);
            return ApiResponse::error('Failed to fetch product list', 500, $th->getMessage());
        }
    }

    public function downloadTemplate(): StreamedResponse {
        $filePath = 'templates/import-product-example.csv';

        if (!Storage::exists($filePath)) {
            return ApiResponse::error('Template file not found', 404);
        }

        return Storage::download($filePath, 'import-product-example.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
