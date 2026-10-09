<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $perPage = Setting::get('rows_per_page', 20);
        $users = User::oldest()->paginate($perPage)
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // raw value (super_admin, admin, etc.)
                'created_at' => $user->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Users/Index', ['users' => $users]);
    }

    public function create()
    {
        $this->authorize('create', User::class);
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,manager,staff,viewer',
        ]);
        if (auth()->user()->hasRole('admin') && $validated['role'] === 'super_admin') {
            abort(403, 'Only a Super Admin can assign the Super Admin role.');
        }
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);
        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return Inertia::render('Users/Edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin,manager,staff,viewer',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if (auth()->user()->hasRole('admin') && $validated['role'] === 'super_admin') {
            abort(403, 'Only a Super Admin can assign the Super Admin role.');
        }
        $data = ['name' => $validated['name'], 'email' => $validated['email'], 'role' => $validated['role']];
        if (!empty($validated['password'])) $data['password'] = Hash::make($validated['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        if ($user->id === auth()->id()) return back()->with('error', 'You cannot delete your own account.');
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}