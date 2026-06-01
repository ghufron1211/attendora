<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'deskripsi',
        'foto_kegiatan',
        'status',
        'komentar_pembimbing',
        'tanda_tangan_admin',
        'tanda_tangan_pembimbing',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Get the user that owns the logbook.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that approved/rejected the logbook.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'user_id', 'user_id')
            ->whereColumn('tanggal', 'logbooks.tanggal');
    }
}
