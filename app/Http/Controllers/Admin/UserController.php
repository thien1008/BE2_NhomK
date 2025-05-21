<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $users = User::where('FullName', 'like', "%{$search}%")
            ->orWhere('Email', 'like', "%{$search}%")
            ->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Email' => 'required|email|unique:users,Email',
            'password' => 'required|string|min:8',
            'Phone' => 'nullable|string|max:20',
            'UserType' => 'required|in:Regular,VIP,Admin',
        ]);

        $data = $request->only(['FullName', 'Email', 'Phone', 'UserType', 'password']);
        
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Email' => 'required|email|unique:users,Email,' . $user->UserID . ',UserID',
            'password' => 'nullable|string|min:8',
            'Phone' => 'nullable|string|max:20',
            'UserType' => 'required|in:Regular,VIP,Admin',
        ]);

        $data = $request->only(['FullName', 'Email', 'Phone', 'UserType']);
        if ($request->filled('password')) {
            Log::info('Updating password for user ID: ' . $user->UserID);
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->UserID === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}