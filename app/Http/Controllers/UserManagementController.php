<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display all users with search and filter.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $instansi = $request->get('instansi', '');
        $role = $request->get('role', 'all');
        $status = $request->get('status', 'all');

        // Start with a clean query builder
        $query = User::query();

        // ── Status Filter ──
        // SoftDeletes trait auto-excludes trashed by default.
        // We only need special handling for 'archived' and 'all'.
        if ($status === 'archived') {
            // Show ONLY soft-deleted users
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            // Show both active AND archived users
            $query->withTrashed();
        }
        // 'active' or default: SoftDeletes already handles it (no extra clause needed)

        // ── Role Filter ──
        // Only apply if a specific role is chosen (not empty, not 'all')
        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        // ── Instansi Filter ──
        // Only apply if a specific instansi is chosen
        if ($instansi && $instansi !== '') {
            $query->where('asal_instansi', $instansi);
        }

        // ── Search Filter ──
        // Only apply if search string is not empty
        if ($search && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('asal_instansi', 'like', '%' . $search . '%');
            });
        }

        // ── Get paginated results ──
        $users = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // ── Instansi dropdown list (include all for dropdown) ──
        $instansiList = User::withTrashed()
            ->select('asal_instansi')
            ->whereNotNull('asal_instansi')
            ->where('asal_instansi', '<>', '')
            ->distinct()
            ->orderBy('asal_instansi', 'asc')
            ->pluck('asal_instansi');

        // ── Statistics (Active users only) ──
        $totalUsers = User::count();
        $totalAdmin = User::where('role', 'admin')->count();

        $totalMahasiswa = User::where('role', 'user')
            ->where(function ($q) {
                $q->where('asal_instansi', 'like', '%Universitas%')
                  ->orWhere('asal_instansi', 'like', '%Institut%')
                  ->orWhere('asal_instansi', 'like', '%Politeknik%')
                  ->orWhere('asal_instansi', 'like', '%Sekolah Tinggi%');
            })->count();

        $totalSiswa = User::where('role', 'user')
            ->where(function ($q) {
                $q->where('asal_instansi', 'like', '%SMK%')
                  ->orWhere('asal_instansi', 'like', '%SMA%')
                  ->orWhere('asal_instansi', 'like', '%MAN%');
            })->count();

        return view('admin.users', compact(
            'users', 'instansiList', 'search', 'instansi', 'role', 'status',
            'totalUsers', 'totalAdmin', 'totalMahasiswa', 'totalSiswa'
        ));
    }

    /**
     * Archive user (soft delete).
     */
    public function destroy($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return redirect('/admin/users')->with('error', 'Tidak bisa mengarsipkan akun Anda sendiri.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->whereNull('deleted_at')->count() <= 1) {
            return redirect('/admin/users')->with('error', 'Tidak bisa mengarsipkan satu-satunya akun admin.');
        }

        $user->delete();

        return redirect('/admin/users')->with('success', 'User "' . $user->name . '" berhasil diarsipkan.');
    }

    /**
     * Restore archived user.
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect('/admin/users')->with('success', 'Akun "' . $user->name . '" berhasil diaktifkan kembali.');
    }

    /**
     * Force delete user permanently.
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect('/admin/users')->with('error', 'Tidak bisa menghapus permanen akun Anda sendiri.');
        }

        $user->forceDelete();

        return redirect('/admin/users')->with('success', 'User "' . $user->name . '" berhasil dihapus secara permanen.');
    }
}
