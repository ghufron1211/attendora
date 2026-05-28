@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Analisis aktivitas kehadiran dan laporan kinerja harian')

@section('content')
<style>
    .mono-card-stat {
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .mono-card-stat:hover {
        transform: translateY(-2px);
        border-color: var(--border-hover) !important;
        box-shadow: var(--shadow-card);
    }
    .heatmap-cell:hover {
        transform: scale(1.25);
        z-index: 10;
        cursor: pointer;
    }
    .calendar-day:hover {
        background: var(--accent-white) !important;
        border-color: var(--border-hover) !important;
        transform: scale(1.05);
    }
    /* Scrollbar Styling */
    #modalUserList::-webkit-scrollbar {
        width: 6px;
    }
    #modalUserList::-webkit-scrollbar-track {
        background: transparent;
    }
    #modalUserList::-webkit-scrollbar-thumb {
        background: var(--border-border);
        border-radius: 4px;
    }
    #modalUserList::-webkit-scrollbar-thumb:hover {
        background: var(--border-hover);
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .user-modal-card:hover {
        background: var(--accent-white) !important;
        border-color: var(--border-hover) !important;
    }
</style>

{{-- Filter Dashboard Form --}}
<form id="filterForm" method="GET" action="/dashboard" style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; background:var(--bg-card); border:1px solid var(--border-border); padding:16px 24px; border-radius:12px; margin-bottom:28px;">
    {{-- Month Filter --}}
    <div style="display:flex; flex-direction:column; gap:6px;">
        <label style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Pilih Bulan</label>
        <select name="month" onchange="document.getElementById('filterForm').submit()" style="background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:8px 14px; border-radius:8px; font-size:12.5px; cursor:pointer; outline:none; transition:all .2s; min-width:140px;" onmouseover="this.style.borderColor='var(--border-hover)'" onmouseout="this.style.borderColor='var(--border-border)'">
            @php
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
            @endphp
            @foreach($months as $num => $name)
                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Year Filter --}}
    <div style="display:flex; flex-direction:column; gap:6px;">
        <label style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Pilih Tahun</label>
        <select name="year" onchange="document.getElementById('filterForm').submit()" style="background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:8px 14px; border-radius:8px; font-size:12.5px; cursor:pointer; outline:none; transition:all .2s;" onmouseover="this.style.borderColor='var(--border-hover)'" onmouseout="this.style.borderColor='var(--border-border)'">
            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    {{-- User Selector (Searchable Modal Trigger - Admin Only) --}}
    @if($user->isAdmin())
    <div style="display:flex; flex-direction:column; gap:6px; flex:1; min-width:240px;">
        <label style="font-size:10px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Pilih Siswa / Mahasiswa</label>
        <input type="hidden" name="user_id" id="hiddenUserId" value="{{ $selectedUserId }}">
        <button type="button" onclick="openUserModal()" style="width: 100%; text-align: left; background: var(--bg-input); border: 1px solid var(--border-border); color: var(--text-foreground); padding: 8px 14px; border-radius: 8px; font-size: 12.5px; cursor: pointer; outline: none; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--border-hover)'" onmouseout="this.style.borderColor='var(--border-border)'">
            <span>
                @if($selectedUserId === 'all')
                    Grafik Keseluruhan (Gabungan Seluruh User)
                @else
                    {{ $selectedUser->name }} ({{ $selectedUser->asal_instansi }})
                @endif
            </span>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
        </button>
    </div>
    @endif

    <noscript>
        <button type="submit" style="padding:8px 16px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; font-size:12.5px; font-weight:600;">Terapkan</button>
    </noscript>
</form>

{{-- SEARCHABLE USER SELECTOR MODAL (Admin Only) --}}
@if($user->isAdmin())
<div id="userModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(12px); z-index: 1000; align-items: center; justify-content: center; padding: 20px; animation: fadeIn 0.2s ease-out;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-border); border-radius: 16px; width: 100%; max-width: 500px; max-height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-card);">
        
        <!-- Modal Header -->
        <div style="padding: 20px; border-bottom: 1px solid var(--border-border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--text-foreground);">Pilih Siswa / Mahasiswa</h3>
            <button type="button" onclick="closeUserModal()" style="background: transparent; border: none; color: var(--text-secondary); cursor: pointer; font-size: 22px; line-height: 1; outline:none;" onmouseover="this.style.color='var(--text-foreground)'" onmouseout="this.style.color='var(--text-secondary)'">&times;</button>
        </div>

        <!-- Search Bar -->
        <div style="padding: 16px; border-bottom: 1px solid var(--border-border); position: relative;">
            <input type="text" id="userSearchInput" oninput="filterUserList()" placeholder="Ketik nama atau asal sekolah/universitas..." style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-border); border-radius: 8px; color: var(--text-foreground); padding: 10px 14px 10px 36px; font-size: 13px; outline: none; transition: border 0.2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
            <svg width="14" height="14" fill="none" stroke="var(--text-secondary)" stroke-width="2.5" viewBox="0 0 24 24" style="position: absolute; left: 26px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>

        <!-- User List -->
        <div id="modalUserList" style="padding: 12px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 8px;">
            <!-- Option for Global Dashboard -->
            <a href="/dashboard?month={{ $month }}&year={{ $year }}&user_id=all" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 10px; border: 1px solid {{ $selectedUserId === 'all' ? 'var(--border-hover)' : 'transparent' }}; background: {{ $selectedUserId === 'all' ? 'var(--accent-active)' : 'transparent' }}; text-decoration: none; transition: all 0.2s;" class="user-modal-card" data-search="keseluruhan gabungan seluruh user">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--text-foreground); display: flex; align-items: center; justify-content: center; color: var(--bg-background); flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 12V7a2 2 0 00-2-2H5a2 2 0 00-2 2v5M21 12H3m18 0v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5"/></svg>
                </div>
                <div>
                    <p style="margin: 0; font-size: 13px; font-weight: 700; color: var(--text-foreground);">Grafik Keseluruhan</p>
                    <p style="margin: 0; font-size: 10.5px; color: var(--text-secondary);">Seluruh data tim digabung</p>
                </div>
            </a>

            @foreach($usersList as $u)
                @php
                    $initials = collect(explode(' ', $u->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                @endphp
                <a href="/dashboard?month={{ $month }}&year={{ $year }}&user_id={{ $u->id }}" style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 10px; border: 1px solid {{ $selectedUserId == $u->id ? 'var(--border-hover)' : 'transparent' }}; background: {{ $selectedUserId == $u->id ? 'var(--accent-active)' : 'transparent' }}; text-decoration: none; transition: all 0.2s;" class="user-modal-card" data-search="{{ strtolower($u->name . ' ' . $u->asal_instansi) }}">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-border); display: flex; align-items: center; justify-content: center; color: var(--text-foreground); font-size: 12.5px; font-weight: 700; text-transform: uppercase; flex-shrink:0;">
                        {{ $initials }}
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 13px; font-weight: 700; color: var(--text-foreground);">{{ $u->name }}</p>
                        <p style="margin: 0; font-size: 10.5px; color: var(--text-secondary);">{{ $u->asal_instansi }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif

@if($selectedUserId === 'all' && $user->isAdmin())
    {{-- ======================================================= --}}
    {{-- LAYOUT 1 — GLOBAL ADMIN DASHBOARD                      --}}
    {{-- ======================================================= --}}
    <div style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:1px;">Statistik Global Tim Bulan Ini</h3>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-7">
        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px 24px; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; right:0; height:3px; background:var(--text-foreground);"></div>
            <p style="font-size:11px; color:var(--text-secondary); margin:0 0 8px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Total User Aktif</p>
            <p style="font-size:32px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-1px;">{{ $totalUsers }} <span style="font-size:14px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Orang</span></p>
        </div>
        
        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px 24px; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent-success);"></div>
            <p style="font-size:11px; color:var(--text-secondary); margin:0 0 8px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Tepat Waktu</p>
            <p style="font-size:32px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-1px;">{{ $tepat }} <span style="font-size:14px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Hari</span></p>
        </div>

        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px 24px; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent-warning);"></div>
            <p style="font-size:11px; color:var(--text-secondary); margin:0 0 8px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Terlambat</p>
            <p style="font-size:32px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-1px;">{{ $terlambat }} <span style="font-size:14px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Hari</span></p>
        </div>

        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px 24px; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent-danger);"></div>
            <p style="font-size:11px; color:var(--text-secondary); margin:0 0 8px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Tidak Hadir</p>
            <p style="font-size:32px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-1px;">{{ $tidakHadir }} <span style="font-size:14px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Hari</span></p>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Tren Absensi Bulanan</h3>
            <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">Perbandingan data kehadiran global 6 bulan terakhir</p>
            <div style="position:relative; width:100%; height:220px;">
                <canvas id="attendanceChart" height="220"></canvas>
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Grafik Keterlambatan</h3>
            <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">Frekuensi kasus keterlambatan global 6 bulan terakhir</p>
            <div style="position:relative; width:100%; height:220px;">
                <canvas id="lateChart" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- Rankings Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-7">
        {{-- ranking terdisiplin --}}
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 16px; font-size:13px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px; display:flex; align-items:center; gap:8px;">
                <svg width="15" height="15" fill="none" stroke="var(--accent-success)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                User Paling Disiplin
            </h3>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @forelse($mostDisciplined as $index => $stat)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:11px; font-weight:800; color:var(--accent-success); width:18px;">#{{ $index + 1 }}</span>
                            <div>
                                <p style="font-size:12px; font-weight:600; margin:0; color:var(--text-foreground);">{{ $stat->user->name ?? 'User Terhapus' }}</p>
                                <p style="font-size:10px; color:var(--text-secondary); margin:0;">{{ $stat->user->asal_instansi ?? '-' }}</p>
                            </div>
                        </div>
                        <span style="font-size:11px; font-weight:600; color:var(--accent-success); background:var(--accent-success-glow); padding:3px 8px; border-radius:6px; border:1px solid rgba(16, 185, 129, 0.15);">{{ $stat->count }} Hari Tepat</span>
                    </div>
                @empty
                    <p style="font-size:12px; color:var(--text-muted); margin:0; text-align:center; padding:12px 0;">Belum ada data tepat waktu bulan ini.</p>
                @endforelse
            </div>
        </div>

        {{-- ranking sering terlambat --}}
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 16px; font-size:13px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px; display:flex; align-items:center; gap:8px;">
                <svg width="15" height="15" fill="none" stroke="var(--accent-warning)" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                User Sering Terlambat
            </h3>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @forelse($mostLate as $index => $stat)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:11px; font-weight:800; color:var(--accent-warning); width:18px;">#{{ $index + 1 }}</span>
                            <div>
                                <p style="font-size:12px; font-weight:600; margin:0; color:var(--text-foreground);">{{ $stat->user->name ?? 'User Terhapus' }}</p>
                                <p style="font-size:10px; color:var(--text-secondary); margin:0;">{{ $stat->user->asal_instansi ?? '-' }}</p>
                            </div>
                        </div>
                        <span style="font-size:11px; font-weight:600; color:var(--accent-warning); background:var(--accent-warning-glow); padding:3px 8px; border-radius:6px; border:1px solid rgba(245, 158, 11, 0.15);">{{ $stat->count }} Hari Lambat</span>
                    </div>
                @empty
                    <p style="font-size:12px; color:var(--text-muted); margin:0; text-align:center; padding:12px 0;">Belum ada data keterlambatan bulan ini.</p>
                @endforelse
            </div>
        </div>

        {{-- persentase kehadiran --}}
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 16px; font-size:13px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px; display:flex; align-items:center; gap:8px;">
                <svg width="15" height="15" fill="none" stroke="var(--text-foreground)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                Persentase Kehadiran
            </h3>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @forelse($userStats as $index => $stat)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:11px; font-weight:800; color:var(--text-foreground); width:18px;">#{{ $index + 1 }}</span>
                            <div>
                                <p style="font-size:12px; font-weight:600; margin:0; color:var(--text-foreground);">{{ $stat->user->name ?? 'User Terhapus' }}</p>
                                <p style="font-size:10px; color:var(--text-secondary); margin:0;">{{ $stat->user->asal_instansi ?? '-' }}</p>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:13px; font-weight:800; color:var(--text-foreground);">{{ $stat->percentage }}%</span>
                            <p style="font-size:9px; color:var(--text-muted); margin:0;">{{ $stat->tepat_count + $stat->lambat_count }}/{{ $stat->total_count }} Hari</p>
                        </div>
                    </div>
                @empty
                    <p style="font-size:12px; color:var(--text-muted); margin:0; text-align:center; padding:12px 0;">Belum ada data absensi bulan ini.</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    {{-- ======================================================= --}}
    {{-- LAYOUT 2 — PERSONAL ATTENDANCE ANALYTICS DASHBOARD       --}}
    {{-- ======================================================= --}}
    
    @php
        $selectedUserInitials = collect(explode(' ', $selectedUser->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
        $presentCount = $tepat + $terlambat;
        $totalDaysCount = $presentCount + $tidakHadir;
        $attendanceRate = $totalDaysCount > 0 ? round(($presentCount / $totalDaysCount) * 100, 1) : 0;
    @endphp

    {{-- SECTION 1 — USER PROFILE SUMMARY --}}
    <div class="flex flex-col lg:flex-row gap-5 lg:gap-7 items-center justify-between p-4 md:p-6 mb-7" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:14px; box-shadow:var(--shadow-subtle);">
        {{-- Profile Info --}}
        <div style="display:flex; flex-direction:column; sm:flex-direction:row; align-items:center; text-align:center; sm:text-left; gap:20px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-border); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--text-foreground); font-size: 20px; text-transform: uppercase; flex-shrink:0;">
                {{ $selectedUserInitials }}
            </div>
            <div>
                <div style="display:flex; flex-direction:column; sm:flex-direction:row; align-items:center; gap:10px; margin-bottom:4px;">
                    <h2 style="margin:0; font-size:18px; font-weight:800; color:var(--text-foreground); letter-spacing:-0.4px;">{{ $selectedUser->name }}</h2>
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 20px; background: var(--accent-success-glow); border: 1px solid rgba(16, 185, 129, 0.15); color: var(--accent-success); font-size: 10px; font-weight: 700;">
                        <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--accent-success);"></span> Aktif
                    </span>
                </div>
                <p style="margin:0 0 4px; font-size:12px; color:var(--text-secondary); font-weight:500;">{{ $selectedUser->asal_instansi }}</p>
                <div style="display:flex; flex-wrap:wrap; justify-content:center; sm:justify-start; gap:12px; font-size:11px; color:var(--text-muted);">
                    <span>Role: <strong style="color:var(--text-secondary);">{{ $selectedUser->role === 'admin' ? 'Mentor' : 'Siswa Magang' }}</strong></span>
                    <span>•</span>
                    <span>Periode: <strong style="color:var(--text-secondary);">{{ $selectedUser->created_at->translatedFormat('M Y') }} - {{ $selectedUser->created_at->addMonths(3)->translatedFormat('M Y') }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Metrics Block --}}
        <div class="flex flex-wrap gap-4 md:gap-7 items-center justify-center text-center w-full lg:w-auto">
            <div style="text-align:center; min-width:80px;">
                <p style="margin:0; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Persentase Kehadiran</p>
                <p style="margin:4px 0 0; font-size:28px; font-weight:800; color:var(--accent-success); letter-spacing:-0.5px;">{{ $attendanceRate }}%</p>
            </div>
            <div class="hidden sm:block" style="width:1px; height:40px; background:var(--border-border);"></div>
            <div style="text-align:center; min-width:60px;">
                <p style="margin:0; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Hadir</p>
                <p style="margin:4px 0 0; font-size:24px; font-weight:800; color:var(--text-foreground);">{{ $presentCount }} <span style="font-size:11px; font-weight:400; color:var(--text-muted);">Hari</span></p>
            </div>
            <div class="hidden sm:block" style="width:1px; height:40px; background:var(--border-border);"></div>
            <div style="text-align:center; min-width:60px;">
                <p style="margin:0; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Telat</p>
                <p style="margin:4px 0 0; font-size:24px; font-weight:800; color:var(--accent-warning);">{{ $terlambat }} <span style="font-size:11px; font-weight:400; color:var(--text-muted);">Hari</span></p>
            </div>
            <div class="hidden sm:block" style="width:1px; height:40px; background:var(--border-border);"></div>
            <div style="text-align:center; min-width:60px;">
                <p style="margin:0; font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:700;">Alpa</p>
                <p style="margin:4px 0 0; font-size:24px; font-weight:800; color:var(--accent-danger);">{{ $tidakHadir }} <span style="font-size:11px; font-weight:400; color:var(--text-muted);">Hari</span></p>
            </div>
        </div>
    </div>

    {{-- SECTION 2 — QUICK STATS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-7">
        <!-- Card 1: Tepat Waktu -->
        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--accent-success-glow); border: 1px solid rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: var(--accent-success); flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div style="flex:1;">
                <p style="font-size:10px; color:var(--text-secondary); margin:0 0 3px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Tepat Waktu</p>
                <p style="font-size:22px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-0.5px;">{{ $tepat }} <span style="font-size:11px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Hari</span></p>
                <div style="width:100%; height:3px; background:var(--bg-input); border-radius:2px; margin-top:8px; overflow:hidden;">
                    <div style="width:{{ $presentCount > 0 ? ($tepat / $presentCount) * 100 : 0 }}%; height:100%; background:var(--accent-success);"></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Terlambat -->
        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--accent-warning-glow); border: 1px solid rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: var(--accent-warning); flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div style="flex:1;">
                <p style="font-size:10px; color:var(--text-secondary); margin:0 0 3px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Terlambat</p>
                <p style="font-size:22px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-0.5px;">{{ $terlambat }} <span style="font-size:11px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Hari</span></p>
                <div style="width:100%; height:3px; background:var(--bg-input); border-radius:2px; margin-top:8px; overflow:hidden;">
                    <div style="width:{{ $presentCount > 0 ? ($terlambat / $presentCount) * 100 : 0 }}%; height:100%; background:var(--accent-warning);"></div>
                </div>
            </div>
        </div>

        <!-- Card 3: Tidak Hadir -->
        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--accent-danger-glow); border: 1px solid rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; color: var(--accent-danger); flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div style="flex:1;">
                <p style="font-size:10px; color:var(--text-secondary); margin:0 0 3px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Tidak Hadir</p>
                <p style="font-size:22px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-0.5px;">{{ $tidakHadir }} <span style="font-size:11px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Hari</span></p>
                <div style="width:100%; height:3px; background:var(--bg-input); border-radius:2px; margin-top:8px; overflow:hidden;">
                    <div style="width:{{ $totalDaysCount > 0 ? ($tidakHadir / $totalDaysCount) * 100 : 0 }}%; height:100%; background:var(--accent-danger);"></div>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Logbook -->
        <div class="mono-card-stat" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--accent-white); border: 1px solid var(--border-border); display: flex; align-items: center; justify-content: center; color: var(--text-foreground); flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div style="flex:1;">
                <p style="font-size:10px; color:var(--text-secondary); margin:0 0 3px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Total Logbook</p>
                <p style="font-size:22px; font-weight:800; margin:0; color:var(--text-foreground); letter-spacing:-0.5px;">{{ $totalLogbook }} <span style="font-size:11px; font-weight:400; color:var(--text-muted); letter-spacing:0;">Entri</span></p>
                <div style="width:100%; height:3px; background:var(--bg-input); border-radius:2px; margin-top:8px; overflow:hidden;">
                    <div style="width:{{ $presentCount > 0 ? ($totalLogbook / $presentCount) * 100 : 0 }}%; height:100%; background:var(--text-foreground);"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Status (Only for User) --}}
    @if(!$user->isAdmin() && $todayAttendance)
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 p-4 md:p-6 mb-7" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="width:8px; height:8px; border-radius:50%; background:var(--accent-success); display:inline-block; animation: pulse 2s infinite;"></span>
            <span style="font-size:12.5px; font-weight:700; color:var(--text-foreground);">Status Absensi Hari Ini</span>
        </div>
        <div class="grid grid-cols-3 gap-4 md:gap-9">
            <div><span style="color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Clock In</span><br><strong style="font-size:14px; color:var(--text-foreground);">{{ $todayAttendance->jam_masuk ? Carbon\Carbon::parse($todayAttendance->jam_masuk)->format('H:i') : '-' }}</strong></div>
            <div><span style="color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Clock Out</span><br><strong style="font-size:14px; color:var(--text-foreground);">{{ $todayAttendance->jam_pulang ? Carbon\Carbon::parse($todayAttendance->jam_pulang)->format('H:i') : '-' }}</strong></div>
            <div><span style="color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Kondisi</span><br>
                @if($todayAttendance->status == 'tepat_waktu')
                    <span style="color:var(--accent-success); font-size:12px; font-weight:700;">Tepat Waktu</span>
                @elseif($todayAttendance->status == 'terlambat')
                    <span style="color:var(--accent-warning); font-size:12px; font-weight:700;">Terlambat</span>
                @else
                    <span style="color:var(--accent-danger); font-size:12px; font-weight:700;">Tidak Hadir</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- SECTION 3 — PERSONAL CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
        {{-- Kiri: Kalender Absensi Bulanan --}}
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Kalender Absensi Harian</h3>
                <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">Status kehadiran harian berdasarkan rekam check-in</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center;">
                <!-- Weekday headers -->
                @foreach(['Se', 'Se', 'Ra', 'Ka', 'Ju', 'Sa', 'Mi'] as $dayName)
                    <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; padding: 4px 0; text-transform: uppercase;">{{ $dayName }}</div>
                @endforeach

                <!-- Empty cells before day 1 -->
                @for($i = 1; $i < $startOfWeek; $i++)
                    <div style="aspect-ratio: 1; border-radius: 6px; background: transparent;"></div>
                @endfor

                <!-- Days in month -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $att = $attendanceMap->get($day);
                        $bg = 'var(--bg-input)';
                        $border = '1px solid var(--border-border)';
                        $textColor = 'var(--text-muted)';
                        $statusDot = '';
                        $dayDate = Carbon\Carbon::create($year, $month, $day);
                        $hName = \App\Helpers\HolidayHelper::getHolidayName($dayDate);

                        if ($att) {
                            $textColor = 'var(--text-foreground)';
                            if ($att->status === 'tepat_waktu') {
                                $bg = 'var(--accent-success-glow)';
                                $border = '1px solid rgba(16, 185, 129, 0.15)';
                                $textColor = 'var(--accent-success)';
                                $statusDot = '<span style="width: 3px; height: 3px; border-radius: 50%; background: var(--accent-success); margin-top: 2px;"></span>';
                            } elseif ($att->status === 'terlambat') {
                                $bg = 'var(--accent-warning-glow)';
                                $border = '1px solid rgba(245, 158, 11, 0.15)';
                                $textColor = 'var(--accent-warning)';
                                $statusDot = '<span style="width: 3px; height: 3px; border-radius: 50%; background: var(--accent-warning); margin-top: 2px;"></span>';
                            } elseif ($att->status === 'tidak_hadir') {
                                $bg = 'var(--accent-danger-glow)';
                                $border = '1px solid rgba(239, 68, 68, 0.15)';
                                $textColor = 'var(--accent-danger)';
                                $statusDot = '<span style="width: 3px; height: 3px; border-radius: 50%; background: var(--accent-danger); margin-top: 2px;"></span>';
                            } elseif ($att->status === 'izin' || $att->status === 'sakit') {
                                $bg = 'var(--accent-info-glow)';
                                $border = '1px solid rgba(59, 130, 246, 0.15)';
                                $textColor = 'var(--accent-info)';
                                $statusDot = '<span style="width: 3px; height: 3px; border-radius: 50%; background: var(--accent-info); margin-top: 2px;"></span>';
                            } elseif ($att->status === 'libur_nasional') {
                                $bg = 'var(--accent-white)';
                                $border = '1px solid var(--border-border)';
                                $textColor = 'var(--text-secondary)';
                                $statusDot = '<span style="width: 3px; height: 3px; border-radius: 50%; background: var(--text-muted); margin-top: 2px;"></span>';
                            }
                        } else {
                            if ($hName) {
                                $bg = 'var(--accent-white)';
                                $border = '1px solid var(--border-border)';
                                $textColor = 'var(--text-foreground)';
                                $statusDot = '<span style="width: 3px; height: 3px; border-radius: 50%; background: var(--text-muted); margin-top: 2px;"></span>';
                            } elseif ($dayDate->isWeekend()) {
                                $bg = 'var(--accent-white)';
                                $border = '1px dashed var(--border-border)';
                                $textColor = 'var(--text-muted)';
                            }
                        }
                    @endphp
                    <div style="aspect-ratio: 1; border-radius: 6px; background: {{ $bg }}; border: {{ $border }}; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; color: {{ $textColor }}; transition: all 0.2s;" class="calendar-day" title="{{ $att ? 'Absensi: ' . str_replace('_', ' ', ucfirst($att->status)) : ($hName ? 'Libur Nasional: ' . $hName : ($dayDate->isWeekend() ? 'Libur Akhir Pekan' : 'Tidak ada catatan')) }}">
                        {{ $day }}
                        {!! $statusDot !!}
                    </div>
                @endfor
            </div>
            
            <div style="display:flex; gap:12px; margin-top:20px; font-size:9.5px; color:var(--text-secondary); justify-content:center; border-top: 1px solid var(--border-border); padding-top:12px; flex-wrap:wrap;">
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:var(--accent-success);"></span> Tepat</span>
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:var(--accent-warning);"></span> Lambat</span>
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:var(--accent-danger);"></span> Alpa</span>
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:var(--accent-info);"></span> Izin/Sakit</span>
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:var(--text-muted);"></span> Libur</span>
            </div>
        </div>

        {{-- Kanan: Trend Jam Kedatangan (Line Chart) --}}
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Trend Jam Kedatangan</h3>
            <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">Grafik fluktuasi waktu clock-in Anda dibanding jam batas masuk</p>
            <div style="position:relative; width:100%; height:220px;">
                <canvas id="lateChart" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- SECTION 4 — ATTENDANCE HEATMAP --}}
    <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px; margin-bottom:28px;">
        <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Heatmap Aktivitas Kehadiran</h3>
        <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">Visualisasi kedisiplinan harian dalam 16 minggu terakhir (Gaya Kontribusi GitHub)</p>
        
        <div style="display: flex; align-items: flex-start; gap: 10px; overflow-x: auto; padding-bottom: 10px;">
            <!-- Day labels on left -->
            <div style="display: flex; flex-direction: column; justify-content: space-between; height: 88px; font-size: 9px; color: var(--text-muted); padding-top: 2px; font-weight: 600;">
                <span>Sen</span>
                <span>Rab</span>
                <span>Jum</span>
                <span>Min</span>
            </div>
            
            <div style="flex: 1;">
                <div style="display: grid; grid-template-rows: repeat(7, 10px); grid-auto-flow: column; gap: 3px; width: max-content;">
                    @foreach($heatmapData as $cell)
                        @php
                            $color = 'var(--bg-input)'; // no record
                            $title = Carbon\Carbon::parse($cell['date'])->translatedFormat('d M Y') . ': Tidak ada record';
                            if ($cell['status'] === 'tepat_waktu') {
                                $color = 'var(--accent-success)'; // Green
                                $title = Carbon\Carbon::parse($cell['date'])->translatedFormat('d M Y') . ': Tepat Waktu';
                            } elseif ($cell['status'] === 'terlambat') {
                                $color = 'var(--accent-warning)'; // Yellow
                                $title = Carbon\Carbon::parse($cell['date'])->translatedFormat('d M Y') . ': Terlambat';
                            } elseif ($cell['status'] === 'tidak_hadir') {
                                $color = 'var(--accent-danger)'; // Red
                                $title = Carbon\Carbon::parse($cell['date'])->translatedFormat('d M Y') . ': Tidak Hadir / Alpa';
                            } elseif ($cell['status'] === 'izin' || $cell['status'] === 'sakit') {
                                $color = 'var(--accent-info)'; // Blue
                                $title = Carbon\Carbon::parse($cell['date'])->translatedFormat('d M Y') . ': ' . ucfirst($cell['status']);
                            } elseif ($cell['status'] === 'libur_nasional' || $cell['status'] === 'libur') {
                                $color = 'var(--text-muted)'; // Gray
                                $hName = \App\Helpers\HolidayHelper::getHolidayName($cell['date']);
                                $title = Carbon\Carbon::parse($cell['date'])->translatedFormat('d M Y') . ': ' . ($hName ? 'Libur Nasional - ' . $hName : 'Libur Akhir Pekan');
                            }
                        @endphp
                        <div style="width: 10px; height: 10px; background: {{ $color }}; border-radius: 2px; transition: transform 0.1s;" class="heatmap-cell" title="{{ $title }}"></div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; font-size: 9px; color: var(--text-secondary); margin-top: 10px; border-top: 1px solid var(--border-border); padding-top: 10px; flex-wrap: wrap;">
            <span style="display:flex; align-items:center; gap:4px;"><div style="width: 8px; height: 8px; background: var(--bg-input); border-radius: 1px; border: 1px solid var(--border-border);"></div> Kosong</span>
            <span style="display:flex; align-items:center; gap:4px;"><div style="width: 8px; height: 8px; background: var(--text-muted); border-radius: 1px;"></div> Libur</span>
            <span style="display:flex; align-items:center; gap:4px;"><div style="width: 8px; height: 8px; background: var(--accent-danger); border-radius: 1px;"></div> Alpa</span>
            <span style="display:flex; align-items:center; gap:4px;"><div style="width: 8px; height: 8px; background: var(--accent-info); border-radius: 1px;"></div> Izin/Sakit</span>
            <span style="display:flex; align-items:center; gap:4px;"><div style="width: 8px; height: 8px; background: var(--accent-warning); border-radius: 1px;"></div> Terlambat</span>
            <span style="display:flex; align-items:center; gap:4px;"><div style="width: 8px; height: 8px; background: var(--accent-success); border-radius: 1px;"></div> Tepat Waktu</span>
        </div>
    </div>

    {{-- SECTION 5 & 6 — RECENT ACTIVITY & PERFORMANCE INSIGHT --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
        <!-- SECTION 5 — RECENT ACTIVITY (Left Card) -->
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Aktivitas Logbook Terbaru</h3>
            <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">5 Laporan logbook kegiatan terakhir yang Anda ajukan</p>
            
            <div style="display:flex; flex-direction:column; gap:16px;">
                @forelse($recentLogbooks as $lb)
                    <div style="display:flex; align-items:flex-start; gap:16px; padding-bottom:16px; border-bottom:1px solid var(--border-border);">
                        <div style="width:70px; height:50px; border-radius:6px; background:var(--bg-input); border:1px solid var(--border-border); overflow:hidden; flex-shrink:0;">
                            @if($lb->foto_kegiatan)
                                <img src="{{ asset('storage/' . $lb->foto_kegiatan) }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:9px; color:var(--text-muted);">No Photo</div>
                            @endif
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <span style="font-size:12px; font-weight:700; color:var(--text-foreground);">{{ $lb->tanggal->translatedFormat('d F Y') }}</span>
                                <span style="font-size:9.5px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid {{ $lb->status === 'approved' ? 'rgba(16, 185, 129, 0.15)' : ($lb->status === 'rejected' ? 'rgba(239, 68, 68, 0.15)' : 'rgba(245, 158, 11, 0.15)') }}; background:{{ $lb->status === 'approved' ? 'var(--accent-success-glow)' : ($lb->status === 'rejected' ? 'var(--accent-danger-glow)' : 'var(--accent-warning-glow)') }}; color:{{ $lb->status === 'approved' ? 'var(--accent-success)' : ($lb->status === 'rejected' ? 'var(--accent-danger)' : 'var(--accent-warning)') }}; text-transform:uppercase; font-size: 8.5px;">
                                    {{ $lb->status }}
                                </span>
                            </div>
                            <p style="margin:0; font-size:11.5px; color:var(--text-secondary); line-height:1.4;">{{ Str::limit($lb->deskripsi, 180) }}</p>
                        </div>
                    </div>
                @empty
                    <p style="font-size:12px; color:var(--text-muted); margin:0; text-align:center; padding:24px 0;">Belum ada logbook yang diisi.</p>
                @endforelse
            </div>
        </div>

        <!-- SECTION 6 — PERFORMANCE INSIGHT (Right Card) -->
        <div style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; padding:24px;">
            <h3 style="margin:0 0 4px; font-size:14px; font-weight:600; color:var(--text-foreground); letter-spacing:-0.2px;">Performance Insights</h3>
            <p style="margin:0 0 20px; font-size:11px; color:var(--text-secondary);">Analisis kedisiplinan otomatis berbasis data absensi</p>
            
            <div style="display:flex; flex-direction:column; gap:16px;">
                <!-- Rata-rata jam datang -->
                <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--accent-white); display:flex; align-items:center; justify-content:center; color:var(--text-foreground); flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <div>
                        <p style="margin:0; font-size:9.5px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Rata-rata Jam Masuk</p>
                        <p style="margin:2px 0 0; font-size:14px; font-weight:700; color:var(--text-foreground);">{{ $avgArrivalTime }} WIB</p>
                    </div>
                </div>

                <!-- Peningkatan Disiplin -->
                <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--accent-success-glow); display:flex; align-items:center; justify-content:center; color:var(--accent-success); flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <p style="margin:0; font-size:9.5px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Tren Kedisiplinan</p>
                        <p style="margin:2px 0 0; font-size:12.5px; font-weight:600; color:var(--text-foreground);">{{ $disciplineImprovement }}</p>
                    </div>
                </div>

                <!-- Total Keterlambatan -->
                <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--accent-warning-glow); display:flex; align-items:center; justify-content:center; color:var(--accent-warning); flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    </div>
                    <div>
                        <p style="margin:0; font-size:9.5px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Total Terlambat Bulan Ini</p>
                        <p style="margin:2px 0 0; font-size:14px; font-weight:700; color:var(--text-foreground);">{{ $terlambat }} Hari</p>
                    </div>
                </div>

                <!-- Ranking Kedisiplinan -->
                <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--accent-white); display:flex; align-items:center; justify-content:center; color:var(--text-foreground); flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <div>
                        <p style="margin:0; font-size:9.5px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Ranking Disiplin</p>
                        <p style="margin:2px 0 0; font-size:14px; font-weight:700; color:var(--text-foreground);">{{ $disciplineRanking }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    const chartData = @json($chartData);
    const chartType = @json($chartType);

    // Searchable Modal Controls
    function openUserModal() {
        document.getElementById('userModal').style.display = 'flex';
        document.getElementById('userSearchInput').focus();
    }
    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
    }
    function filterUserList() {
        const input = document.getElementById('userSearchInput').value.toLowerCase();
        const cards = document.getElementsByClassName('user-modal-card');
        for (let i = 0; i < cards.length; i++) {
            const searchData = cards[i].getAttribute('data-search');
            if (searchData.includes(input)) {
                cards[i].style.display = 'flex';
            } else {
                cards[i].style.display = 'none';
            }
        }
    }

    // Modal click-outside logic
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target == modal) {
            closeUserModal();
        }
    }

    // Shared tooltip config — dark premium corporate style
    const tooltipConfig = {
        enabled: true,
        backgroundColor: '#1a1a1a',
        titleColor: '#ffffff',
        bodyColor: '#9ca3af',
        borderColor: '#2a2a2a',
        borderWidth: 1,
        cornerRadius: 8,
        padding: 12,
        titleFont: { family: 'Inter', size: 12, weight: '600' },
        bodyFont: { family: 'Inter', size: 11, weight: '400' },
        displayColors: true,
        boxPadding: 4,
        usePointStyle: true,
        caretSize: 6,
        callbacks: {
            label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                    label += ': ';
                }
                
                // Special formatting for clock in times (floats back to time string)
                if (chartType === 'daily' && context.dataset.id === 'clock_in_times') {
                    if (context.parsed.y !== null) {
                        const decimalTime = context.parsed.y;
                        const hours = Math.floor(decimalTime);
                        const minutes = Math.round((decimalTime - hours) * 60);
                        label += String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ' WIB';
                    } else {
                        label += '-';
                    }
                } else {
                    label += context.parsed.y;
                }
                return label;
            }
        }
    };

    // Render Global Attendance Chart (Only in Global Mode)
    if (document.getElementById('attendanceChart')) {
        window.attendanceChartInstance = new Chart(document.getElementById('attendanceChart'), {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Tepat Waktu',
                        data: chartData.tepat,
                        backgroundColor: 'rgba(34,197,94,0.8)',
                        hoverBackgroundColor: '#22c55e',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.65
                    },
                    {
                        label: 'Terlambat',
                        data: chartData.terlambat,
                        backgroundColor: 'rgba(250,204,21,0.75)',
                        hoverBackgroundColor: '#facc15',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.65
                    },
                    {
                        label: 'Tidak Hadir',
                        data: chartData.tidak_hadir,
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        hoverBackgroundColor: '#ef4444',
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.7,
                        categoryPercentage: 0.65
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 750, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: tooltipConfig,
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            color: '#9ca3af',
                            font: { family: 'Inter', size: 10.5, weight: '500' },
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 14,
                            usePointStyle: true,
                            pointStyle: 'rectRounded'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#666', font: { family: 'Inter', size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#666', font: { family: 'Inter', size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }
                    }
                }
            }
        });
    }

    // Render Late / Clock-In Trend Chart
    const lateChartDataset = [];
    let lateChartOptions = {};

    if (chartType === 'daily') {
        // Daily Drilldown: Line chart displaying exact Clock-In time hours
        lateChartDataset.push({
            id: 'clock_in_times',
            label: 'Jam Kedatangan',
            data: chartData.clock_in_times,
            borderColor: '#ffffff',
            backgroundColor: (ctx) => {
                const chart = ctx.chart;
                const { ctx: c, chartArea } = chart;
                if (!chartArea) return 'rgba(255,255,255,0.01)';
                const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(255,255,255,0.08)');
                gradient.addColorStop(1, 'rgba(255,255,255,0.005)');
                return gradient;
            },
            fill: true,
            tension: 0.3,
            spanGaps: true,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#111111',
            pointBorderWidth: 1.5,
            pointRadius: 3.5,
            pointHoverRadius: 5.5,
            pointHoverBackgroundColor: '#ffffff',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
            borderWidth: 2
        });

        // Add Target lines
        lateChartDataset.push({
            label: 'Batas Tepat Waktu (08:00)',
            data: Array(chartData.labels.length).fill(8.00),
            borderColor: 'rgba(34, 197, 94, 0.4)',
            borderWidth: 1.2,
            borderDash: [4, 4],
            pointRadius: 0,
            fill: false,
        });

        lateChartDataset.push({
            label: 'Toleransi Terlambat (08:15)',
            data: Array(chartData.labels.length).fill(8.25),
            borderColor: 'rgba(250, 204, 21, 0.4)',
            borderWidth: 1.2,
            borderDash: [4, 4],
            pointRadius: 0,
            fill: false,
        });

        // Config specific for daily Clock-In Y axis
        lateChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: tooltipConfig,
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#9ca3af',
                        font: { family: 'Inter', size: 10, weight: '500' },
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#666', font: { family: 'Inter', size: 10 } },
                    grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }
                },
                y: {
                    min: 7, // 07:00
                    max: 10, // 10:00
                    ticks: {
                        color: '#666',
                        font: { family: 'Inter', size: 10 },
                        stepSize: 0.5,
                        callback: function(value) {
                            const hours = Math.floor(value);
                            const minutes = Math.round((value - hours) * 60);
                            return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
                        }
                    },
                    grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }
                }
            }
        };
    } else {
        // Global Trend: Line chart displaying number of tardy events per month
        lateChartDataset.push({
            id: 'late_count',
            label: 'Total Terlambat',
            data: chartData.terlambat,
            borderColor: '#facc15',
            backgroundColor: (ctx) => {
                const chart = ctx.chart;
                const { ctx: c, chartArea } = chart;
                if (!chartArea) return 'rgba(250,204,21,0.01)';
                const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(250,204,21,0.12)');
                gradient.addColorStop(1, 'rgba(250,204,21,0.01)');
                return gradient;
            },
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#facc15',
            pointBorderColor: '#111111',
            pointBorderWidth: 2,
            pointRadius: 4.5,
            pointHoverRadius: 6.5,
            pointHoverBackgroundColor: '#facc15',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
            borderWidth: 2.2
        });

        lateChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: tooltipConfig,
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#9ca3af',
                        font: { family: 'Inter', size: 10.5, weight: '500' },
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 14,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#666', font: { family: 'Inter', size: 10 } },
                    grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#666', font: { family: 'Inter', size: 10 } },
                    grid: { color: 'rgba(255,255,255,0.02)', drawBorder: false }
                }
            }
        };
    }

    window.lateChartInstance = new Chart(document.getElementById('lateChart'), {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: lateChartDataset
        },
        options: lateChartOptions
    });

    // Dynamic Chart Theme Updater
    window.updateChartsForTheme = function(theme) {
        const isLight = theme === 'light';
        const textColor = isLight ? '#495057' : '#9ca3af';
        const gridColor = isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.02)';
        
        [window.attendanceChartInstance, window.lateChartInstance].forEach(chart => {
            if (chart) {
                chart.options.plugins.legend.labels.color = textColor;
                chart.options.scales.x.ticks.color = '#666';
                chart.options.scales.x.grid.color = gridColor;
                chart.options.scales.y.ticks.color = '#666';
                chart.options.scales.y.grid.color = gridColor;
                
                if (chart.config.type === 'line' && chart.data.datasets[0] && chart.data.datasets[0].id === 'clock_in_times') {
                    chart.data.datasets[0].borderColor = isLight ? '#111111' : '#ffffff';
                    chart.data.datasets[0].pointBackgroundColor = isLight ? '#111111' : '#ffffff';
                    chart.data.datasets[0].pointHoverBackgroundColor = isLight ? '#111111' : '#ffffff';
                }
                chart.update();
            }
        });
    };

    // Initialize chart colors immediately on load if light mode is active
    if (localStorage.getItem('theme') === 'light') {
        window.updateChartsForTheme('light');
    }
</script>
@endpush
