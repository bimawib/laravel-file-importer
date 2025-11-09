<?php

namespace App\Services;

use App\Models\ImportJob;

class ImportJobService
{

    public function listImportJobs($request) {
        $query = ImportJob::query()->orderBy('created_at', 'desc');

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->get('entity_type'));
        }

        $jobs = $query->paginate($request->get('limit', 20));

        return [
            'data' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total()
            ]
        ];
    }

    public function getImportJobStatus(ImportJob $job) {
        return [
            'job_id' => $job->id,
            'status' => $job->status,
            'total' => $job->total,
            'success' => $job->success,
            'failed' => $job->failed,
            'updated_at' => $job->updated_at->format('Y-m-d H:i:s')
        ];
    }
}