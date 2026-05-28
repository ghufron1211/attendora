<?php

namespace App\Http\Controllers;

use App\Exports\LogbookExport;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Export logbook data to Excel (.xlsx) with embedded images.
     */
    public function exportExcel(Request $request)
    {
        // If accessed directly without query parameters, show the multi-month filter view
        if (!$request->has('user_id')) {
            $users = User::where('role', 'user')->orderBy('name', 'asc')->get();
            return view('admin.export', compact('users'));
        }

        // Validate the request inputs for date range
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_month' => 'required|integer|between:1,12',
            'start_year' => 'required|integer|min:2020|max:2050',
            'end_month' => 'required|integer|between:1,12',
            'end_year' => 'required|integer|min:2020|max:2050',
        ], [
            'user_id.required' => 'Pengguna wajib dipilih.',
            'user_id.exists' => 'Pengguna tidak valid.',
            'start_month.required' => 'Bulan awal wajib dipilih.',
            'start_year.required' => 'Tahun awal wajib dipilih.',
            'end_month.required' => 'Bulan akhir wajib dipilih.',
            'end_year.required' => 'Tahun akhir wajib dipilih.',
        ]);

        $userId = (int) $request->get('user_id');
        $startMonth = (int) $request->get('start_month');
        $startYear = (int) $request->get('start_year');
        $endMonth = (int) $request->get('end_month');
        $endYear = (int) $request->get('end_year');

        // Check if start date is before end date
        $startDate = Carbon::create($startYear, $startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($endYear, $endMonth, 1)->endOfMonth();

        if ($startDate->gt($endDate)) {
            return back()->withErrors(['start_month' => 'Periode mulai tidak boleh melebihi periode akhir.']);
        }

        $targetUser = User::findOrFail($userId);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $cleanUserName = preg_replace('/[^A-Za-z0-9]/', '', $targetUser->name);
        $startMonthName = $monthNames[$startMonth] ?? $startMonth;
        $endMonthName = $monthNames[$endMonth] ?? $endMonth;

        // Generate descriptive clean filename
        if ($startYear === $endYear) {
            $fileName = 'Logbook_' . $cleanUserName . '_' . $startMonthName . '-' . $endMonthName . '_' . $startYear . '.xlsx';
        } else {
            $fileName = 'Logbook_' . $cleanUserName . '_' . $startMonthName . $startYear . '-' . $endMonthName . $endYear . '.xlsx';
        }

        return Excel::download(
            new LogbookExport($userId, $startMonth, $startYear, $endMonth, $endYear),
            $fileName
        );
    }
}
