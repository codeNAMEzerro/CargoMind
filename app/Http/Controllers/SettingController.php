<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan (Hanya untuk Master).
     */
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $users = User::orderBy('name')->get();

        return view('settings.index', compact('settings', 'users'));
    }

    /**
     * Perbarui pengaturan toko.
     */
    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string|max:500',
            'store_phone' => 'required|string|max:50',
            'receipt_font_size' => 'required|integer|min:10|max:24',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        $keys = ['store_name', 'store_address', 'store_phone', 'receipt_font_size', 'receipt_footer'];

        foreach ($keys as $key) {
            Setting::setValue($key, $request->get($key));
        }

        ActivityLog::log('update_settings', 'Pengaturan toko diperbarui oleh ' . auth()->user()->name);

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    /**
     * Buat user baru (Hanya untuk Master).
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:master,manager,karyawan',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        ActivityLog::log('create_user', "User baru dibuat: {$user->name} ({$user->role})", User::class, $user->id);

        return back()->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Hapus user (Hanya untuk Master).
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        ActivityLog::log('delete_user', "User dihapus: {$user->name} ({$user->role})", User::class, $user->id);

        $user->delete();

        return back()->with('success', 'User berhasil dihapus!');
    }
}
