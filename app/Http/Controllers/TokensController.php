<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TokensController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('user:token');

        /** @var User $user */
        $user = $request->user();

        return response()->json($user->tokens()->get([
            'name', 'abilities', 'created_at', 'last_used_at',
        ]));
    }

    public function store(Request $request)
    {
        Gate::authorize('user:token');

        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name'    => 'nullable|max:30',
            'roles'   => 'array',
            'roles.*' => [
                Rule::in($user->roles),
            ],
        ]);

        if (empty($data['name'])) {
            $time         = now()->toISOString();
            $data['name'] = "Key-$time";
        }

        $token = $user->createToken($data['name'], $data['roles']);

        return response()->json($token);
    }
}
