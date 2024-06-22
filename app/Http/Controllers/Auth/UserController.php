<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; // Correct import
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect('/register')
                        ->withErrors($validator)
                        ->withInput();
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);
            return redirect()->intended('dashboard');
        } catch (Exception $e) {
            Log::error('User registration error: ' . $e->getMessage());
            return redirect('/register')->with('error', 'Failed to register, please try again.');
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return redirect('/login')
                        ->withErrors($validator)
                        ->withInput();
        }

        $credentials = $request->only('email', 'password');

        try {
            if (Auth::attempt($credentials)) {
                return redirect()->intended('dashboard');
            } else {
                return redirect('/login')->with('error', 'Invalid email or password.');
            }
        } catch (Exception $e) {
            Log::error('User login error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Failed to login, please try again.');
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            return redirect('/login')->with('success', 'Successfully logged out.');
        } catch (Exception $e) {
            Log::error('User logout error: ' . $e->getMessage());
            return redirect('/dashboard')->with('error', 'Failed to logout, please try again.');
        }
    }

    public function showProfile()
    {
        try {
            $user = Auth::user();
            return view('profile', compact('user'));
        } catch (Exception $e) {
            Log::error('Show profile error: ' . $e->getMessage());
            return redirect('/dashboard')->with('error', 'Failed to load profile, please try again.');
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return redirect('/profile')
                        ->withErrors($validator)
                        ->withInput();
        }

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            return redirect('/profile')->with('success', 'Profile updated successfully.');
        } catch (Exception $e) {
            Log::error('Update profile error: ' . $e->getMessage());
            return redirect('/profile')->with('error', 'Failed to update profile, please try again.');
        }
    }
}
