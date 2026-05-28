<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display holiday list.
     */
    public function index(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $search = $request->get('search', '');

        $query = Holiday::query();

        if ($year !== 'all') {
            $query->whereYear('tanggal', $year);
        }

        if ($search !== '') {
            $query->where('nama_libur', 'like', "%{$search}%");
        }

        $holidays = $query->orderBy('tanggal', 'asc')->paginate(25)->withQueryString();

        return view('admin.holidays.index', compact('holidays', 'year', 'search'));
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:holidays,tanggal',
            'nama_libur' => 'required|string|max:255',
            'tipe' => 'required|string|in:nasional,khusus',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.unique' => 'Tanggal libur ini sudah terdaftar.',
            'nama_libur.required' => 'Nama libur wajib diisi.',
            'tipe.required' => 'Tipe libur wajib diisi.',
        ]);

        Holiday::create($request->only('tanggal', 'nama_libur', 'tipe'));

        return redirect()->route('admin.holidays')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:holidays,tanggal,' . $holiday->id,
            'nama_libur' => 'required|string|max:255',
            'tipe' => 'required|string|in:nasional,khusus',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.unique' => 'Tanggal libur ini sudah terdaftar.',
            'nama_libur.required' => 'Nama libur wajib diisi.',
            'tipe.required' => 'Tipe libur wajib diisi.',
        ]);

        $holiday->update($request->only('tanggal', 'nama_libur', 'tipe'));

        return redirect()->route('admin.holidays')->with('success', 'Hari libur berhasil diperbarui.');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.holidays')->with('success', 'Hari libur berhasil dihapus.');
    }
}
