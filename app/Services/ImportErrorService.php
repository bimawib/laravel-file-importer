<?php

namespace App\Services;

use App\Models\ImportError;

class ImportErrorService
{
    public function listImportErrors($request) {
        $query = ImportError::query();

        if ($request->has('import_job_id')) {
            $query->where('import_job_id', $request->get('import_job_id'));
        }

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->get('entity_type'));
        }

        $errors = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 20));

        return [
            'data' => $errors->items(),
            'pagination' => [
                'current_page' => $errors->currentPage(),
                'last_page' => $errors->lastPage(),
                'per_page' => $errors->perPage(),
                'total' => $errors->total()
            ]
        ];
    } 
}