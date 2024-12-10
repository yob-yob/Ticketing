<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required', // Since I don't know how this API will be consumed, I'll just assume this will be consumed by another 3rd party that we don't have access to.
        ]);
     
        $user = User::where('email', $data['email'])->first();
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
     
        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken
        ]);
    }
}
