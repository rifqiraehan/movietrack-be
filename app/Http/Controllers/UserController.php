<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserUpdateRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\UserRegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User::where('username', $data['username'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'username' => [
                        'Username already exists'
                    ]
                ]
            ], 400));
        }

        if (User::where('email', $data['email'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => [
                        'Email already exists'
                    ]
                ]
            ], 400));
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'Username or password wrong'
                    ]
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(200);
    }

    public function get(Request $request): UserResource
    {
        $user = Auth::user();

        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();

        $user = Auth::user();

        if (isset($data['username']) && $data['username'] != $user->username) {
            if (User::where('username', $data['username'])->count() == 1) {
                throw new HttpResponseException(response([
                    'errors' => [
                        'username' => [
                            'Username already exists'
                        ]
                    ]
                ], 400));
            }
        }

        if (isset($data['email']) && $data['email'] != $user->email) {
            if (User::where('email', $data['email'])->count() == 1) {
                throw new HttpResponseException(response([
                    'errors' => [
                        'email' => [
                            'Email already exists'
                        ]
                    ]
                ], 400));
            }
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('pfp')) {
            $file = $request->file('pfp');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid()->toString() . '.' . $extension;
            $file->storeAs('pfps', $filename);
            $data['pfp'] = $filename;

            if ($user->pfp) {
                Storage::delete('pfps/' . basename($user->pfp));

            }
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->token = null;
        $user->save();

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }
}
