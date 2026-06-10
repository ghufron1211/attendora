<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogbookController extends Controller
{
    /**
     * User's logbook list.
     */
    public function index()
    {
        $logbooks = Logbook::where('user_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('logbook.index', compact('logbooks'));
    }

    /**
     * Show logbook creation form.
     */
    public function create()
    {
        $now = now();
        if (\App\Helpers\HolidayHelper::isWeekend($now)) {
            return redirect('/logbook')->with('error', 'Hari ini adalah libur akhir pekan (Sabtu/Minggu). Pengisian logbook dinonaktifkan.');
        }
        if ($holidayName = \App\Helpers\HolidayHelper::getHolidayName($now)) {
            return redirect('/logbook')->with('error', 'Hari ini adalah Hari Libur Nasional: ' . $holidayName . '. Pengisian logbook dinonaktifkan.');
        }

        $today = $now->toDateString();

        // Check if already submitted today
        $existing = Logbook::where('user_id', Auth::id())
            ->where('tanggal', $today)
            ->first();

        if ($existing) {
            return redirect('/logbook')->with('error', 'Anda sudah membuat logbook untuk hari ini.');
        }

        return view('logbook.create');
    }

    /**
     * Store logbook entry.
     */
    public function store(Request $request)
    {
        $now = now();
        if (\App\Helpers\HolidayHelper::isWeekend($now)) {
            return redirect('/logbook')->with('error', 'Hari ini adalah libur akhir pekan (Sabtu/Minggu). Pengisian logbook dinonaktifkan.');
        }
        if ($holidayName = \App\Helpers\HolidayHelper::getHolidayName($now)) {
            return redirect('/logbook')->with('error', 'Hari ini adalah Hari Libur Nasional: ' . $holidayName . '. Pengisian logbook dinonaktifkan.');
        }

        $request->validate([
            'deskripsi' => 'required|string|max:2000',
            'foto_kegiatan' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'deskripsi.required' => 'Deskripsi kegiatan wajib diisi.',
            'foto_kegiatan.required' => 'Foto kegiatan wajib diunggah.',
            'foto_kegiatan.image' => 'File harus berupa gambar.',
            'foto_kegiatan.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $today = $now->toDateString();

        // Double check uniqueness
        $existing = Logbook::where('user_id', Auth::id())
            ->where('tanggal', $today)
            ->first();

        if ($existing) {
            return redirect('/logbook')->with('error', 'Logbook untuk hari ini sudah ada.');
        }

        // Store photo
        $path = $request->file('foto_kegiatan')->store('logbooks', 'public');

        Logbook::create([
            'user_id' => Auth::id(),
            'tanggal' => $today,
            'deskripsi' => $request->deskripsi,
            'foto_kegiatan' => $path,
            'status' => 'pending',
        ]);

        return redirect('/logbook')->with('success', 'Logbook berhasil disimpan.');
    }

    public function adminIndex(Request $request)
    {
        $now = now();

        // Determine if month/year filters are explicitly provided
        $hasMonthFilter = $request->has('month');
        $hasYearFilter = $request->has('year');

        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);

        $query = Logbook::with('user', 'admin')
            ->orderBy('tanggal', 'desc');

        // Only apply month/year filter when explicitly requested
        if ($hasMonthFilter || $hasYearFilter) {
            $query->whereMonth('tanggal', $month)
                  ->whereYear('tanggal', $year);
        }

        if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $logbooks = $query->paginate(20);

        // Eager load attendance manually using dates and user IDs
        $userIds = $logbooks->pluck('user_id')->unique();
        $dates = $logbooks->pluck('tanggal')->map(fn($d) => $d->toDateString())->unique();

        $attendances = \App\Models\Attendance::whereIn('user_id', $userIds)
            ->whereIn('tanggal', $dates)
            ->get()
            ->groupBy(fn($item) => $item->user_id . '_' . \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d'));

        foreach ($logbooks as $lb) {
            $key = $lb->user_id . '_' . $lb->tanggal->format('Y-m-d');
            $lb->setRelation('attendance', isset($attendances[$key]) ? $attendances[$key]->first() : null);
        }

        return view('logbook.admin', compact('logbooks', 'month', 'year'));
    }

    /**
     * Admin: approve logbook.
     */
    public function approve(Request $request, Logbook $logbook)
    {
        $request->validate([
            'tanda_tangan' => 'required|string',
        ]);

        $logbook->update([
            'status' => 'approved',
            'tanda_tangan_admin' => $request->tanda_tangan,
            'admin_id' => Auth::id(),
        ]);

        return redirect('/admin/logbook')->with('success', 'Logbook berhasil di-approve.');
    }

    /**
     * Admin: reject logbook.
     */
    public function reject(Logbook $logbook)
    {
        $logbook->update([
            'status' => 'rejected',
            'admin_id' => Auth::id(),
        ]);

        return redirect('/admin/logbook')->with('success', 'Logbook berhasil di-reject.');
    }
}
