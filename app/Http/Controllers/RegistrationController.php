<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users',
            'password' => ['required', new Password(8), 'confirmed'],
            'password_confirmation' => 'required',
        ]);

        $user = User::create($data);

        return $user; // We may want to return a sanctum token here instead...
    }
}
