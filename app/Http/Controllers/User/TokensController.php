<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserTokenResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TokensController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user   = $request->user();
        $result = UserTokenResource::collection($user->tokens()->get());

        return response()->json($result);
    }

    public function store(Request $request)
    {
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
            $data['name'] = "Key-{$time}";
        }

        $token = $user->createToken($data['name'], $data['roles']);

        return response()->json($token);
    }

    public function destroy(Request $request, int $token)
    {
        /** @var User $user */
        $user = $request->user();

        $token = $user->tokens()->find($token);
        if (empty($token)) {
            throw new NotFoundHttpException();
        }

        $token->delete();
    }
}
