<?php

namespace App\Services;

use App\Http\Requests\RegisterNewUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function register(RegisterNewUserRequest $request) {
        
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password'])
        ]);

        $guestRole = Role::firstOrCreate(['name' => 'guest']);

        $user->assignRole($guestRole);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'created_at' => $user->created_at->toDateTimeString(),
        ];
    }

    public function listUsers(Request $request): array {
        $limit = $request->get('limit', 20);
        $search = $request->get('search');
        $sort = $request->get('sort', 'asc');

        $users = User::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', $sort)
            ->paginate($limit);

        return [
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'created_at' => $user->created_at?->toDateTimeString()
                ];
            }),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total()
            ],
        ];
    }
}