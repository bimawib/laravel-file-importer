<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Jobs\ProcessProductImportJob;
use App\Models\ImportJob;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductService
{
    public function importProducts(Request $request) {
        $request->validate([
            'file' => [
                'required',
                'mimes:csv',
                'max:5120'
            ]
        ]);

        $path = $request->file('file')->store('imports/products');

        $job = ImportJob::create([
            'filename' => $path,
            'status' => 'pending',
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'entity_type' => 'products'
        ]);

        ProcessProductImportJob::dispatch($job->id);

        return [
            'data' => [
                'job_id' => $job->id,
                'status' => $job->status
            ],
            'message' => 'Your file upload has been processed.'
        ];
    }

    public function listProducts($request) {
        $perPage = $request->get('limit', 20);
        $products = Product::orderBy('created_at', 'desc')->paginate($perPage);

        return [
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total()
            ]
        ];
    }
}