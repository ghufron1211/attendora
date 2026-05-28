<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $attendances = Attendance::with('user')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('tanggal', 'desc')
                ->limit(100)
                ->get();
        } else {
            $attendances = Attendance::with('user')
                ->where('user_id', $user->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('tanggal', 'desc')
                ->limit(50)
                ->get();
        }

        $markers = $attendances->map(function ($att) {
            return [
                'lat' => (float) $att->latitude,
                'lng' => (float) $att->longitude,
                'name' => $att->user->name ?? 'Unknown',
                'tanggal' => $att->tanggal->format('d M Y'),
                'jam_masuk' => $att->jam_masuk ?? '-',
                'status' => $att->status,
            ];
        });

        return view('map.index', compact('markers'));
    }
}
