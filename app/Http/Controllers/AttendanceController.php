<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\HolidayHelper;

class AttendanceController extends Controller
{
    // Office location
    const OFFICE_LAT = -6.2355;
    const OFFICE_LNG = 106.8260;
    const MAX_RADIUS = 100; // meters
    const MAX_ACCURACY = 100; // meters

    // Jam kerja (WIB)
    const JAM_MASUK = '08:00';
    const TOLERANSI_MASUK = '08:15';
    const JAM_PULANG = '16:00';
    const TOLERANSI_PULANG = '17:00';

    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $todayAttendance = Attendance::where('user_id', $user->id)->where('tanggal', $today)->first();
        $history = Attendance::where('user_id', $user->id)->orderBy('tanggal', 'desc')->limit(30)->get();
        $userFaceData = $user->face_data;

        $now = Carbon::now('Asia/Jakarta');
        $isWeekend = $now->isWeekend();
        $holidayName = HolidayHelper::getHolidayName($now);
        $isHoliday = $holidayName !== null;

        return view('attendance.index', compact('todayAttendance', 'history', 'userFaceData', 'isWeekend', 'isHoliday', 'holidayName'));
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'required|numeric',
            'foto' => 'required|string',
            'face_match' => 'required|boolean',
        ]);

        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Check if weekend or holiday
        if ($now->isWeekend()) {
            return response()->json(['error' => 'Hari Sabtu dan Minggu adalah hari libur pekanan.'], 422);
        }

        if ($holidayName = HolidayHelper::getHolidayName($now)) {
            return response()->json(['error' => 'Hari ini adalah hari libur nasional: ' . $holidayName . '.'], 422);
        }

        // Check if already clocked in (1x per hari)
        $existing = Attendance::where('user_id', $user->id)->where('tanggal', $today)->first();
        if ($existing && $existing->jam_masuk) {
            return response()->json(['error' => 'Anda sudah melakukan clock in hari ini.'], 422);
        }

        // Validate GPS accuracy
        if ($request->accuracy > self::MAX_ACCURACY) {
            return response()->json(['error' => 'Akurasi GPS terlalu rendah (' . round($request->accuracy) . 'm). Maksimal ' . self::MAX_ACCURACY . 'm.'], 422);
        }

        // Validate distance
        $distance = $this->haversine($request->latitude, $request->longitude, self::OFFICE_LAT, self::OFFICE_LNG);
        if ($distance > self::MAX_RADIUS) {
            return response()->json(['error' => 'Anda berada di luar radius kantor (' . round($distance) . 'm). Maksimal ' . self::MAX_RADIUS . 'm.'], 422);
        }

        // Validate face match
        if (!$request->face_match) {
            return response()->json(['error' => 'Wajah tidak cocok dengan data registrasi.'], 422);
        }

        // Save photo
        $fotoPath = null;
        if ($request->foto) {
            $image = $request->foto;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $fileName = 'attendance/' . $user->id . '_' . $today . '_in.png';
            Storage::disk('public')->put($fileName, base64_decode($image));
            $fotoPath = $fileName;
        }

        // Determine status berdasarkan WIB
        $jamMasuk = $now->format('H:i:s');
        $batasToleransi = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . self::TOLERANSI_MASUK, 'Asia/Jakarta');

        // <= 08:15 → tepat_waktu, > 08:15 → terlambat
        if ($now->lte($batasToleransi)) {
            $status = 'tepat_waktu';
        } else {
            $status = 'terlambat';
        }

        // Device info
        $deviceInfo = json_encode([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accuracy' => $request->accuracy,
            'distance' => round($distance, 2),
            'timezone' => 'Asia/Jakarta',
            'clock_in_time' => $now->toDateTimeString(),
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => $today],
            [
                'jam_masuk' => $jamMasuk,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'foto' => $fotoPath,
                'status' => $status,
                'device_info' => $deviceInfo,
            ]
        );

        $statusLabel = $status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat';

        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil! Jam: ' . $now->format('H:i') . ' WIB — Status: ' . $statusLabel,
            'attendance' => $attendance,
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('tanggal', $today)->first();

        // Harus sudah clock in dulu
        if (!$attendance || !$attendance->jam_masuk) {
            return response()->json(['error' => 'Anda belum melakukan clock in hari ini.'], 422);
        }

        // Hanya 1x clock out per hari
        if ($attendance->jam_pulang) {
            return response()->json(['error' => 'Anda sudah melakukan clock out hari ini.'], 422);
        }

        // Validasi jam pulang: minimal 16:00 WIB
        $batasPulang = Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . self::JAM_PULANG, 'Asia/Jakarta');
        if ($now->lt($batasPulang)) {
            $sisaWaktu = $now->diff($batasPulang);
            $sisaFormatted = '';
            if ($sisaWaktu->h > 0) $sisaFormatted .= $sisaWaktu->h . ' jam ';
            if ($sisaWaktu->i > 0) $sisaFormatted .= $sisaWaktu->i . ' menit';
            return response()->json([
                'error' => 'Clock out hanya bisa dilakukan setelah pukul ' . self::JAM_PULANG . ' WIB. Sisa waktu: ' . trim($sisaFormatted) . '.'
            ], 422);
        }

        // Clock out diperbolehkan dari 16:00 ke atas (termasuk lewat 17:00)
        $attendance->update([
            'jam_pulang' => $now->format('H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clock out berhasil! Jam pulang: ' . $now->format('H:i') . ' WIB',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Calculate Haversine distance in meters.
     */
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
