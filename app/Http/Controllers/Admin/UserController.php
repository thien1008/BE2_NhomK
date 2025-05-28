<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        try {
            $validated = $request->validate([
                'FullName' => 'required|string|max:255',
                'Email' => 'required|email|unique:users,Email',
                'password' => 'required|string|min:8',
                'Phone' => 'nullable|string|max:20',
                'UserType' => 'required|in:Regular,VIP,Admin',
            ]);

            $data = $request->only(['FullName', 'Email', 'Phone', 'UserType', 'password']);
            $data['version'] = 1; // Initialize version

            User::create($data);

            return response()->json(['success' => 'Người dùng đã được tạo thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo người dùng.'], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'FullName' => 'required|string|max:255',
                'Email' => 'required|email|unique:users,Email,' . $user->UserID . ',UserID',
                'password' => 'nullable|string|min:8',
                'Phone' => 'nullable|string|max:20',
                'UserType' => 'required|in:Regular,VIP,Admin',
                'version' => 'required|integer|min:1',
            ]);

            // Check version for optimistic locking
            if ($user->version != $request->version) {
                return response()->json(['error' => 'Dữ liệu người dùng đã được thay đổi bởi người khác. Vui lòng làm mới trang và thử lại.'], 409);
            }

            $data = $request->only(['FullName', 'Email', 'Phone', 'UserType']);
            if ($request->filled('password')) {
                Log::info('Updating password for user ID: ' . $user->UserID);
                $data['password'] = $request->password;
            }
            $data['version'] = $user->version + 1; // Increment version

            $user->update($data);

            return response()->json(['success' => 'Người dùng đã được cập nhật thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật người dùng.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'Người dùng không tồn tại.'], 404);
            }

            if ($user->UserID === auth()->id()) {
                return response()->json(['error' => 'Bạn không thể xóa tài khoản của chính mình.'], 403);
            }

            $user->delete();

            return response()->json(['success' => 'Người dùng đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa người dùng.'], 500);
        }
    }
}