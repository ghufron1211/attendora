<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Holiday;

class HolidayHelper
{
    /**
     * In-memory cache for holidays to avoid O(N) database queries during calendar loops.
     */
    protected static ?array $cachedHolidays = null;

    /**
     * Check if a given date is a national holiday and return its name.
     */
    public static function getHolidayName(string|Carbon $date): ?string
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        
        if (self::$cachedHolidays === null) {
            if (!\Illuminate\Support\Facades\Schema::hasTable('holidays')) {
                return null;
            }
            self::$cachedHolidays = Holiday::pluck('nama_libur', 'tanggal')
                ->mapWithKeys(function($name, $tanggal) {
                    $key = Carbon::parse($tanggal)->format('Y-m-d');
                    return [$key => $name];
                })->toArray();
        }

        return self::$cachedHolidays[$dateStr] ?? null;
    }

    /**
     * Check if a date is weekend (Saturday or Sunday).
     */
    public static function isWeekend(string|Carbon $date): bool
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbonDate->isWeekend();
    }
}
