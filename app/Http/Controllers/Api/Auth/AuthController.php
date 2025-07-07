<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;

/**
 * @group User
 * Endpoints for user authentication and profile management.
 */
class AuthController extends Controller
{
    /**
     * @group Auth
     * Log in a user.
     *
     * @bodyParam email string required The user's email address. Example: john.doe@example.com
     * @bodyParam password string required The user's password. Example: password123
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

      
        $user = User::where('email', $request->email)->first();

      
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        
        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @group Auth
     * Register a new user.
     *
     * @bodyParam name string required The user's name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john.doe@example.com
     * @bodyParam password string required The user's password. Must be confirmed. Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Must match the password. Example: password123
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('User');

        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Log out
     *
     * Revokes the current access token and logs out the user.
     */
    public function logout(Request $request)
    {
       
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get user profile
     *
     * Returns the details of the currently authenticated user.
     */
    public function user(Request $request)
    {
     
        return new UserResource($request->user());
    }

    /**
     * Update user profile
     *
     * Allows the user to update their name and email address.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->update($validatedData);

    
        return new UserResource($user);
    }
}
