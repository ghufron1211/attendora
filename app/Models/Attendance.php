<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'latitude',
        'longitude',
        'foto',
        'status',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the user that owns the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by today.
     */
    public function scopeToday($query)
    {
        return $query->where('tanggal', now()->toDateString());
    }

    /**
     * Scope to filter by month and year.
     */
    public function scopeByMonth($query, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }
}
