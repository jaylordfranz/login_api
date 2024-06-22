<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; // Import the User model
use Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Exception $e) {
            // Log the error message
            Log::error('Google redirect error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Failed to initiate Google login, please try again.');
        }
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

            if (!$user || !isset($user->id)) {
                throw new Exception('Failed to retrieve user information from Google.');
            }

            // Use the User model instead of UserController
            $finduser = User::where('google_id', $user->id)->first();

            if ($finduser) {
                Auth::login($finduser);
                return redirect()->intended('dashboard');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => encrypt('my-google') // Set a default or random password
                ]);

                Auth::login($newUser);
                return redirect()->intended('dashboard');
            }
        } catch (Exception $e) {
            // Log the exception message for debugging
            Log::error('Google login error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Failed to login using Google, please try again.');
        }
    }
}