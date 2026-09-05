<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends AdminController
{
    /**
     * لیست کاربران
     */
    public function index(Request $request)
    {
        $query = User::query();

        // فیلتر بر اساس نقش
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // جستجو
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('core::admin.users.index', compact('users'));
    }

    /**
     * نمایش فرم ایجاد کاربر
     */
    public function create()
    {
        $roles = ['admin', 'editor', 'user', 'guest'];
        return view('core::admin.users.create', compact('roles'));
    }

    /**
     * ذخیره کاربر جدید
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,editor,user,guest',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', "کاربر '{$user->name}' با موفقیت ایجاد شد.");
    }

    /**
     * نمایش جزئیات کاربر
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('core::admin.users.show', compact('user'));
    }

    /**
     * نمایش فرم ویرایش کاربر
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = ['admin', 'editor', 'user', 'guest'];
        return view('core::admin.users.edit', compact('user', 'roles'));
    }

    /**
     * بروزرسانی کاربر
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,editor,user,guest',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "کاربر '{$user->name}' با موفقیت بروزرسانی شد.");
    }

    /**
     * حذف کاربر
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // جلوگیری از حذف کاربر فعلی
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'نمی‌توانید کاربر خود را حذف کنید.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "کاربر '{$user->name}' با موفقیت حذف شد.");
    }
}
