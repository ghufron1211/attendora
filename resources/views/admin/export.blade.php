@extends('layouts.app')
@section('title', 'Export Laporan')
@section('subtitle', 'Unduh laporan logbook dan absensi harian per user untuk jangka panjang')

@section('content')
<div style="max-width:580px; width:100%; margin:0 auto; padding-top:20px;">
    <div class="p-5 md:p-8" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:14px; box-shadow:var(--shadow-card);">
        
        {{-- Card Header --}}
        <div style="text-align:center; margin-bottom:28px; border-bottom:1px solid var(--border-border); padding-bottom:20px;">
            <div style="width:48px; height:48px; background:var(--bg-input); border-radius:12px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px; border:1px solid var(--border-border);">
                <svg width="22" height="22" fill="none" stroke="var(--text-foreground)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </div>
            <h3 style="margin:0 0 6px; font-size:16px; font-weight:700; color:var(--text-foreground); letter-spacing:-0.2px;">Format Ekspor Magang / PKL</h3>
            <p style="font-size:12px; color:var(--text-secondary); margin:0;">Mendukung rentang waktu bulanan untuk kebutuhan laporan semesteran mahasiswa/siswa.</p>
        </div>

        {{-- Error Display --}}
        @if($errors->any())
        <div style="padding:12px 16px; border-radius:8px; margin-bottom:20px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.15); color:#ef4444; font-size:12.5px;">
            @foreach($errors->all() as $error)
                <p style="margin:0;">• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Export Form --}}
        <form action="/admin/export/excel" method="GET" style="display:flex; flex-direction:column; gap:20px;">
            
            {{-- User Select --}}
            <div style="display:flex; flex-direction:column; gap:8px;">
                <label style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Pilih Siswa / Mahasiswa</label>
                <select name="user_id" required style="width:100%; background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:10px 14px; border-radius:8px; font-size:13px; cursor:pointer; outline:none; transition:all .2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
                    <option value="" disabled selected>-- Pilih Pengguna --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id || request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->asal_instansi }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Start Period Select --}}
            <div style="border-top:1px solid var(--border-border); padding-top:16px;">
                <label style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:700; display:block; margin-bottom:12px;">Mulai Dari Periode</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Start Month --}}
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Bulan Mulai</span>
                        <select name="start_month" required style="width:100%; background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:10px 14px; border-radius:8px; font-size:13px; cursor:pointer; outline:none; transition:all .2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
                            @php
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                $defaultStartMonth = request('month', now()->month);
                            @endphp
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ old('start_month', $defaultStartMonth) == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Start Year --}}
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Tahun Mulai</span>
                        <select name="start_year" required style="width:100%; background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:10px 14px; border-radius:8px; font-size:13px; cursor:pointer; outline:none; transition:all .2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
                            @php
                                $defaultStartYear = request('year', now()->year);
                            @endphp
                            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ old('start_year', $defaultStartYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            {{-- End Period Select --}}
            <div style="border-top:1px solid var(--border-border); padding-top:16px; margin-bottom:10px;">
                <label style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:700; display:block; margin-bottom:12px;">Sampai Dengan Periode</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- End Month --}}
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Bulan Akhir</span>
                        <select name="end_month" required style="width:100%; background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:10px 14px; border-radius:8px; font-size:13px; cursor:pointer; outline:none; transition:all .2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
                            @php
                                $defaultEndMonth = request('month', now()->month);
                            @endphp
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ old('end_month', $defaultEndMonth) == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- End Year --}}
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Tahun Akhir</span>
                        <select name="end_year" required style="width:100%; background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:10px 14px; border-radius:8px; font-size:13px; cursor:pointer; outline:none; transition:all .2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
                            @php
                                $defaultEndYear = request('year', now()->year);
                            @endphp
                            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ old('end_year', $defaultEndYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" style="width:100%; padding:12px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13.5px; transition:all .2s; display:inline-flex; align-items:center; justify-content:center; gap:8px;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh Laporan Gabungan (.xlsx)
            </button>
        </form>
    </div>
</div>
@endsection
