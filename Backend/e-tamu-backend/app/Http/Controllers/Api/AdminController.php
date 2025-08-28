<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Koki Baru: AdminController
 * Bertanggung jawab atas semua logika manajemen pengguna dan divisi.
 * Hanya Super Admin yang boleh memberikan perintah pada koki ini.
 */
class AdminController extends Controller
{
    /**
     * Menyediakan data awal untuk halaman manajemen (daftar user & divisi).
     */
    public function getManagementData()
    {
        // Pastikan hanya super_admin yang bisa mengakses
        if (auth()->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::with('division')->get();
        $divisions = Division::all();

        return response()->json([
            'users' => $users,
            'divisions' => $divisions,
        ]);
    }

    /**
     * Membuat pengguna (user) baru.
     */
    public function storeUser(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'division_id' => $request->role === 'admin' ? $request->division_id : null,
        ]);

        return response()->json(['message' => 'Pengguna berhasil ditambahkan.', 'user' => $user->load('division')], 201);
    }

    /**
     * Menghapus pengguna (user).
     */
    public function destroyUser(User $user)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mencegah super admin menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }

    /**
     * Membuat divisi baru.
     */
    public function storeDivision(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:divisions',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $division = Division::create($request->only('name'));

        return response()->json(['message' => 'Divisi berhasil ditambahkan.', 'division' => $division], 201);
    }

    /**
     * Menghapus divisi.
     */
    public function destroyDivision(Division $division)
    {
        if (auth()->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Tambahan: Cek jika ada user yang masih terhubung dengan divisi ini
        if ($division->users()->count() > 0) {
            return response()->json(['message' => 'Divisi tidak dapat dihapus karena masih ada pengguna yang terhubung.'], 409);
        }

        $division->delete();

        return response()->json(['message' => 'Divisi berhasil dihapus.']);
    }
}
