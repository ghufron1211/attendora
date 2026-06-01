<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Logbook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Jakarta');
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        $selectedUserId = $request->get('user_id', 'all');

        // Fetch users list for dropdown filter (admin only)
        $usersList = [];
        if ($user->isAdmin()) {
            $usersList = User::where('role', 'user')->orderBy('name', 'asc')->get();
        }

        // Initialize personal data variables with defaults
        $selectedUser = null;
        $totalLogbook = 0;
        $recentLogbooks = collect();
        $avgArrivalTime = '-';
        $disciplineImprovement = '';
        $disciplineRanking = '';
        $attendanceMap = collect();
        $heatmapData = [];
        $startOfWeek = 1;
        $daysInMonth = 30;
        $izin = 0;
        $sakit = 0;
        $liburNasional = 0;

        if ($user->isAdmin()) {
            // ─── ADMIN DASHBOARD ───
            $totalUsers = User::where('role', 'user')->count();

            // Statistics based on whether all users or a single user is selected
            if ($selectedUserId === 'all') {
                // Global stats for selected month/year
                $attendances = Attendance::whereMonth('tanggal', $month)->whereYear('tanggal', $year);
                $tepat = (clone $attendances)->where('status', 'tepat_waktu')->count();
                $terlambat = (clone $attendances)->where('status', 'terlambat')->count();
                $tidakHadir = (clone $attendances)->whereIn('status', ['tidak_hadir', 'izin', 'sakit'])->count();
                $izin = (clone $attendances)->where('status', 'izin')->count();
                $sakit = (clone $attendances)->where('status', 'sakit')->count();
                $liburNasional = (clone $attendances)->where('status', 'libur_nasional')->count();

                // 6 months global trend chart data
                $chartData = $this->getMonthlyTrendChartData(null, 6);
                $chartType = 'global';
            } else {
                // Specific user stats for selected month/year
                $selectedUser = User::findOrFail($selectedUserId);
                $personalData = $this->getPersonalAnalyticsData($selectedUser, $month, $year);

                $tepat = $personalData['tepat'];
                $terlambat = $personalData['terlambat'];
                $tidakHadir = $personalData['tidakHadir'];
                $izin = $personalData['izin'];
                $sakit = $personalData['sakit'];
                $liburNasional = $personalData['liburNasional'];
                $totalLogbook = $personalData['totalLogbook'];
                $recentLogbooks = $personalData['recentLogbooks'];
                $avgArrivalTime = $personalData['avgArrivalTime'];
                $disciplineImprovement = $personalData['disciplineImprovement'];
                $disciplineRanking = $personalData['disciplineRanking'];
                $attendanceMap = $personalData['attendanceMap'];
                $heatmapData = $personalData['heatmapData'];
                $startOfWeek = $personalData['startOfWeek'];
                $daysInMonth = $personalData['daysInMonth'];

                // Daily chart data for this specific user
                $chartData = $this->getDailyChartData($selectedUserId, $month, $year);
                $chartType = 'daily';
            }

            // Rankings and statistics (always computed globally for the selected month/year)
            // 1. Most disciplined (most tepat_waktu)
            $mostDisciplined = Attendance::select('user_id', DB::raw('count(*) as count'))
                ->where('status', 'tepat_waktu')
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->with('user')
                ->limit(5)
                ->get();

            // 2. Most late (most terlambat)
            $mostLate = Attendance::select('user_id', DB::raw('count(*) as count'))
                ->where('status', 'terlambat')
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->with('user')
                ->limit(5)
                ->get();

            // 3. User attendance percentage list
            $userStats = Attendance::select('user_id',
                    DB::raw("SUM(CASE WHEN status = 'tepat_waktu' THEN 1 ELSE 0 END) as tepat_count"),
                    DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as lambat_count"),
                    DB::raw("SUM(CASE WHEN status IN ('tidak_hadir', 'izin', 'sakit') THEN 1 ELSE 0 END) as alpa_count")
                )
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->groupBy('user_id')
                ->with('user')
                ->get()
                ->map(function($stat) {
                    $present = $stat->tepat_count + $stat->lambat_count;
                    $total = $present + $stat->alpa_count;
                    $stat->percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                    $stat->total_count = $total;
                    return $stat;
                })
                ->sortByDesc('percentage')
                ->values()
                ->take(5);

            $todayAttendance = null;
        } else {
            // ─── USER DASHBOARD ───
            $totalUsers = null;
            $selectedUser = $user;
            $personalData = $this->getPersonalAnalyticsData($selectedUser, $month, $year);

            $tepat = $personalData['tepat'];
            $terlambat = $personalData['terlambat'];
            $tidakHadir = $personalData['tidakHadir'];
            $izin = $personalData['izin'];
            $sakit = $personalData['sakit'];
            $liburNasional = $personalData['liburNasional'];
            $totalLogbook = $personalData['totalLogbook'];
            $recentLogbooks = $personalData['recentLogbooks'];
            $avgArrivalTime = $personalData['avgArrivalTime'];
            $disciplineImprovement = $personalData['disciplineImprovement'];
            $disciplineRanking = $personalData['disciplineRanking'];
            $attendanceMap = $personalData['attendanceMap'];
            $heatmapData = $personalData['heatmapData'];
            $startOfWeek = $personalData['startOfWeek'];
            $daysInMonth = $personalData['daysInMonth'];

            // Daily chart data for this user
            $chartData = $this->getDailyChartData($user->id, $month, $year);
            $chartType = 'daily';

            // Today's attendance status
            $todayAttendance = Attendance::where('user_id', $user->id)
                ->where('tanggal', $now->toDateString())
                ->first();

            $mostDisciplined = null;
            $mostLate = null;
            $userStats = null;
        }

        return view('dashboard.index', compact(
            'user', 'totalUsers', 'tepat', 'terlambat', 'tidakHadir', 'izin', 'sakit', 'liburNasional',
            'chartData', 'chartType', 'todayAttendance', 'month', 'year',
            'selectedUserId', 'usersList', 'mostDisciplined', 'mostLate', 'userStats',
            'selectedUser', 'totalLogbook', 'recentLogbooks', 'avgArrivalTime',
            'disciplineImprovement', 'disciplineRanking', 'attendanceMap',
            'heatmapData', 'startOfWeek', 'daysInMonth'
        ));
    }

    /**
     * Get or build personal analytics data for a user.
     */
    private function getPersonalAnalyticsData($selectedUser, $month, $year)
    {
        $now = Carbon::now('Asia/Jakarta');

        // Fetch attendances for specific month/year
        $attendancesQuery = Attendance::where('user_id', $selectedUser->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year);

        $tepat = (clone $attendancesQuery)->where('status', 'tepat_waktu')->count();
        $terlambat = (clone $attendancesQuery)->where('status', 'terlambat')->count();
        $tidakHadir = (clone $attendancesQuery)->whereIn('status', ['tidak_hadir', 'izin', 'sakit'])->count();
        $izin = (clone $attendancesQuery)->where('status', 'izin')->count();
        $sakit = (clone $attendancesQuery)->where('status', 'sakit')->count();
        $liburNasional = (clone $attendancesQuery)->where('status', 'libur_nasional')->count();

        // 1. Total Logbook
        $totalLogbook = Logbook::where('user_id', $selectedUser->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->count();

        // 2. Recent logbooks
        $recentLogbooks = Logbook::where('user_id', $selectedUser->id)
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        // 3. Average arrival time
        $arrivalTimes = Attendance::where('user_id', $selectedUser->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->whereNotNull('jam_masuk')
            ->pluck('jam_masuk');

        $avgArrivalTime = '-';
        if ($arrivalTimes->isNotEmpty()) {
            $totalSeconds = 0;
            foreach ($arrivalTimes as $time) {
                $parts = explode(':', $time);
                $totalSeconds += ($parts[0] * 3600) + ($parts[1] * 60) + ($parts[2] ?? 0);
            }
            $avgSeconds = $totalSeconds / $arrivalTimes->count();
            $avgHours = floor($avgSeconds / 3600);
            $avgMins = floor(($avgSeconds % 3600) / 60);
            $avgArrivalTime = sprintf('%02d:%02d', $avgHours, $avgMins);
        }

        // 4. Discipline improvement comparison
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        
        $prevAttendances = Attendance::where('user_id', $selectedUser->id)
            ->whereMonth('tanggal', $prevMonth)
            ->whereYear('tanggal', $prevYear);
            
        $prevTepat = (clone $prevAttendances)->where('status', 'tepat_waktu')->count();
        $prevTerlambat = (clone $prevAttendances)->where('status', 'terlambat')->count();
        $prevAlpa = (clone $prevAttendances)->where('status', 'tidak_hadir')->count();
        $prevTotal = $prevTepat + $prevTerlambat + $prevAlpa;
        $prevLateRate = $prevTotal > 0 ? ($prevTerlambat / $prevTotal) * 100 : 0;
        
        $totalHadir = $tepat + $terlambat;
        $currTotal = $totalHadir + $tidakHadir;
        $currLateRate = $currTotal > 0 ? ($terlambat / $currTotal) * 100 : 0;
        
        if ($prevTotal == 0) {
            $disciplineImprovement = "Stabilitas awal (belum ada pembanding)";
        } else {
            $diff = $prevLateRate - $currLateRate;
            if ($diff > 0) {
                $disciplineImprovement = "Disiplin naik " . round($diff, 1) . "% vs bulan lalu";
            } elseif ($diff < 0) {
                $disciplineImprovement = "Disiplin turun " . round(abs($diff), 1) . "% vs bulan lalu";
            } else {
                $disciplineImprovement = "Disiplin stabil vs bulan lalu";
            }
        }

        // 5. Discipline ranking
        $allUserStats = Attendance::select('user_id',
                DB::raw("SUM(CASE WHEN status = 'tepat_waktu' THEN 1 ELSE 0 END) as tepat_count"),
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as lambat_count"),
                DB::raw("SUM(CASE WHEN status IN ('tidak_hadir', 'izin', 'sakit') THEN 1 ELSE 0 END) as alpa_count")
            )
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->groupBy('user_id')
            ->get()
            ->map(function($stat) {
                $present = $stat->tepat_count + $stat->lambat_count;
                $total = $present + $stat->alpa_count;
                $stat->percentage = $total > 0 ? ($present / $total) * 100 : 0;
                return $stat;
            })
            ->sortByDesc(fn($stat) => $stat->percentage * 1000 + $stat->tepat_count)
            ->values();

        $disciplineRanking = '-';
        foreach ($allUserStats as $idx => $stat) {
            if ($stat->user_id == $selectedUser->id) {
                $disciplineRanking = '#' . ($idx + 1) . ' dari ' . $allUserStats->count() . ' user';
                break;
            }
        }

        // 6. Attendance Map for calendar grid
        $attendanceMap = Attendance::where('user_id', $selectedUser->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get()
            ->keyBy(fn($item) => (int) $item->tanggal->format('d'));

        // 7. Heatmap data (last 16 weeks)
        $endDateHeatmap = Carbon::now('Asia/Jakarta')->endOfWeek();
        $startDateHeatmap = $endDateHeatmap->copy()->subWeeks(16)->startOfWeek();

        $heatmapAttendances = Attendance::where('user_id', $selectedUser->id)
            ->whereBetween('tanggal', [$startDateHeatmap->toDateString(), $endDateHeatmap->toDateString()])
            ->get()
            ->keyBy(fn($item) => $item->tanggal->format('Y-m-d'));

        $heatmapData = [];
        $currentDay = $startDateHeatmap->copy();
        while ($currentDay->lte($endDateHeatmap)) {
            $dateStr = $currentDay->toDateString();
            $att = $heatmapAttendances->get($dateStr);
            
            $status = 'none';
            if ($att) {
                $status = $att->status;
            } else {
                if (\App\Helpers\HolidayHelper::getHolidayName($currentDay) || \App\Helpers\HolidayHelper::isWeekend($currentDay)) {
                    $status = 'libur';
                }
            }
            
            $heatmapData[] = [
                'date' => $dateStr,
                'status' => $status,
                'dayOfWeek' => $currentDay->dayOfWeekIso,
            ];
            $currentDay->addDay();
        }

        // 8. Calendar metrics
        $firstDayOfMonth = Carbon::create($year, $month, 1);
        $startOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 = Mon, 7 = Sun
        $daysInMonth = $firstDayOfMonth->daysInMonth;

        return [
            'tepat' => $tepat,
            'terlambat' => $terlambat,
            'tidakHadir' => $tidakHadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'liburNasional' => $liburNasional,
            'totalLogbook' => $totalLogbook,
            'recentLogbooks' => $recentLogbooks,
            'avgArrivalTime' => $avgArrivalTime,
            'disciplineImprovement' => $disciplineImprovement,
            'disciplineRanking' => $disciplineRanking,
            'attendanceMap' => $attendanceMap,
            'heatmapData' => $heatmapData,
            'startOfWeek' => $startOfWeek,
            'daysInMonth' => $daysInMonth,
        ];
    }

    private function getMonthlyTrendChartData($userId = null, $months = 6)
    {
        $labels = [];
        $tepatData = [];
        $terlambatData = [];
        $tidakHadirData = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Jakarta')->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $query = Attendance::whereMonth('tanggal', $date->month)->whereYear('tanggal', $date->year);
            if ($userId) {
                $query->where('user_id', $userId);
            }

            $tepatData[] = (clone $query)->where('status', 'tepat_waktu')->count();
            $terlambatData[] = (clone $query)->where('status', 'terlambat')->count();
            $tidakHadirData[] = (clone $query)->where('status', 'tidak_hadir')->count();
        }

        return [
            'labels' => $labels,
            'tepat' => $tepatData,
            'terlambat' => $terlambatData,
            'tidak_hadir' => $tidakHadirData,
        ];
    }

    private function getDailyChartData($userId, $month, $year)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        
        $labels = [];
        $tepatData = [];
        $terlambatData = [];
        $tidakHadirData = [];
        $clockInTimes = [];
        
        $attendances = Attendance::where('user_id', $userId)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get()
            ->keyBy(fn($item) => (int) $item->tanggal->format('d'));

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = "H-$day";
            
            $att = $attendances->get($day);
            if ($att) {
                $tepatData[] = $att->status === 'tepat_waktu' ? 1 : 0;
                $terlambatData[] = $att->status === 'terlambat' ? 1 : 0;
                $tidakHadirData[] = $att->status === 'tidak_hadir' ? 1 : 0;
                
                if ($att->jam_masuk) {
                    $parts = explode(':', $att->jam_masuk);
                    $hours = (int) $parts[0];
                    $minutes = (int) ($parts[1] ?? 0);
                    $clockInTimes[] = round($hours + ($minutes / 60), 2);
                } else {
                    $clockInTimes[] = null;
                }
            } else {
                $tepatData[] = 0;
                $terlambatData[] = 0;
                $tidakHadirData[] = 0;
                $clockInTimes[] = null;
            }
        }
        
        return [
            'labels' => $labels,
            'tepat' => $tepatData,
            'terlambat' => $terlambatData,
            'tidak_hadir' => $tidakHadirData,
            'clock_in_times' => $clockInTimes,
        ];
    }
}
