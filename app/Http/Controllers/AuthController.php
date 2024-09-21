<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showUserInfo(Request $request)
    {
        return response()->json($request->user(), 200);
    }
    public function getUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'user' => $user,
        ], 200);
    }
    public function getAllUsers()
    {
        $users = User::all();

        return response()->json([
            'users' => $users,
        ], 200);
    }



    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email|max:255|exists:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $data['email'])->first();

        // Check if the password matches
        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        try {
            $data['password'] = Hash::make($data['password']);

            // Create new user
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password'];

            $user->save();

            // Create token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201); // Change the status code to 201
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User registration failed!',
                'error' => $e->getMessage(),
            ], 409);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
    

    public function updateUser(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        try {
            if (isset($data['name'])) {
                $user->name = $data['name'];
            }
            if (isset($data['email'])) {
                $user->email = $data['email'];
            }

            $user->save();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User update failed!',
                'error' => $e->getMessage(),
            ], 409);
        }
    }
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Check if the current password matches
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        try {
            $user->password = Hash::make($data['new_password']);
            $user->save();

            return response()->json([
                'message' => 'Password changed successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password change failed!',
                'error' => $e->getMessage(),
            ], 409);
        }
    }
}
