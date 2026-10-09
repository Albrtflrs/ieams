<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function edit()
    {
        return Inertia::render('Profile/Edit', [
            'user' => auth()->user()->only('id', 'name', 'email', 'avatar_path'),
        ]);
    }

    public function update(Request $request)
    {
        Log::info('=== PROFILE UPDATE STARTED ===');
        Log::info('Has file: ' . ($request->hasFile('avatar') ? 'YES' : 'NO'));

        $user = auth()->user();

        $validated = $request->validate([
            'name'   => 'nullable|string|max:255',
            'email'  => 'nullable|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->filled('name')) {
            $user->name = $validated['name'];
        }

        if ($request->filled('email') && $user->hasRole(['super_admin', 'admin'])) {
            $user->email = $validated['email'];
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
                Log::info('Old avatar deleted');
            }

            // Ensure directory exists
            if (!Storage::disk('public')->exists('profile_photos')) {
                Storage::disk('public')->makeDirectory('profile_photos');
                Log::info('Directory created');
            }

            // Store new avatar
            $path = $request->file('avatar')->store('profile_photos', 'public');
            $user->avatar_path = $path;
            Log::info('Avatar saved at: ' . $path);
        }

        $user->save();
        Log::info('User saved. avatar_path = ' . ($user->avatar_path ?? 'NULL'));

        return redirect()->route('profile.edit')->with('success', 'Profile updated.');
    }

    public function showAvatar($filename)
{
    $path = 'profile_photos/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
}
}