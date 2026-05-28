@extends('layouts.app')
@section('title', 'Logbook')
@section('subtitle', 'Catatan kegiatan harian Anda')

@section('content')
@php
    $now = now();
    $isWeekend = \App\Helpers\HolidayHelper::isWeekend($now);
    $holidayName = \App\Helpers\HolidayHelper::getHolidayName($now);
    $isHoliday = $holidayName !== null;
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h3 style="margin:0;font-size:15px;font-weight:600;color:var(--text-foreground);letter-spacing:-0.2px;">Daftar Logbook</h3>
    @if($isWeekend || $isHoliday)
        <span style="font-size:11px;color:var(--text-muted);background:var(--bg-input);border:1px solid var(--border-border);padding:6px 14px;border-radius:8px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
            <span style="width:6px;height:6px;border-radius:50%;background:var(--text-muted);"></span>
            Hari Libur (Pengisian Logbook Non-Aktif)
        </span>
    @else
        <a href="/logbook/create" style="padding:9px 18px;background:var(--text-foreground);color:var(--bg-background);border:none;border-radius:8px;text-decoration:none;font-weight:600;font-size:12px;transition:all .2s;display:inline-flex;align-items:center;gap:6px;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">+ Tambah Logbook</a>
    @endif
</div>

<div class="responsive-table" style="background:var(--bg-card);border:1px solid var(--border-border);border-radius:12px;overflow:hidden;">
    <table class="mono-table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Tanggal</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Deskripsi</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Foto</th>
                <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logbooks as $lb)
            <tr>
                <td data-label="Tanggal" style="padding:11px 16px;border-bottom:1px solid var(--border-border);font-size:13px;color:var(--text-foreground);">{{ $lb->tanggal->format('d M Y') }}</td>
                <td data-label="Deskripsi" style="padding:11px 16px;border-bottom:1px solid var(--border-border);font-size:13px;color:var(--text-secondary);max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $lb->deskripsi }}</td>
                <td data-label="Foto" style="padding:11px 16px;border-bottom:1px solid var(--border-border);">
                    <img src="{{ asset('storage/'.$lb->foto_kegiatan) }}" style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--border-border);filter:grayscale(30%);" alt="foto">
                </td>
                <td data-label="Status" style="padding:11px 16px;border-bottom:1px solid var(--border-border);">
                    @if($lb->status=='pending')<span class="badge-warning" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Pending</span>
                    @elseif($lb->status=='approved')<span class="badge-success" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Approved</span>
                    @else<span class="badge-danger" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Rejected</span>@endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">Belum ada logbook.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $logbooks->links() }}</div>
@endsection
