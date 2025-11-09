<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePermissionWebRequest;
use App\Http\Requests\UpdatePermissionWebRequest;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PermissionService
{

    public function listPermissions(Request $request): array {
        $limit = $request->get('limit', 20);
        $search = $request->get('search');
        $sort = $request->get('sort', 'asc');

        $permissions = Permission::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name', $sort)
            ->paginate($limit);

        return [
            'data' => $permissions->items(),
            'pagination' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
            ],
        ];
    }

    public function createPermission(string $name): Permission {
        return Permission::create(['name' => $name]);
    }

    public function updatePermission(Permission $permission, string $name): Permission {
        $permission->update(['name' => $name]);
        return $permission;
    }
}