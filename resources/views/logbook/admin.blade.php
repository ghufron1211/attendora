@extends('layouts.app')
@section('title', 'Review Logbook')
@section('subtitle', 'Sistem verifikasi aktivitas harian dan kehadiran magang siswa/mahasiswa')

@section('content')
<style>
    .sticky-filter-panel {
        position: sticky;
        top: 68px;
        z-index: 15;
        backdrop-filter: blur(16px);
        background: var(--bg-card-glass);
        padding: 20px 24px;
        border: 1px solid var(--border-border);
        border-radius: 12px;
        margin-bottom: 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    @media (max-width: 768px) {
        .sticky-filter-panel {
            flex-direction: column;
            align-items: stretch;
        }
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filter-label {
        font-size: 10px;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
    }
    .filter-select {
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        color: var(--text-foreground);
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12.5px;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
        padding-right: 32px;
    }
    .filter-select:focus {
        border-color: var(--border-hover);
    }
    
    .btn-export-excel {
        padding: 9px 16px;
        background: var(--text-foreground);
        color: var(--bg-background);
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12.5px;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-export-excel:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: var(--shadow-subtle);
    }
    
    .logbook-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    @media (min-width: 640px) {
        .logbook-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }
    }
    @media (min-width: 1024px) {
        .logbook-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 28px;
        }
    }
    .premium-logbook-card {
        background: var(--bg-card);
        border: 1px solid var(--border-border);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .premium-logbook-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 16px;
        padding: 1px;
        background: linear-gradient(180deg, var(--border-glass), transparent 50%);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .premium-logbook-card:hover {
        transform: translateY(-5px);
        border-color: var(--border-hover);
        box-shadow: var(--shadow-card);
    }
    
    .avatar-initials {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-foreground);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    
    .date-badge {
        font-size: 11px;
        color: var(--text-secondary);
        font-weight: 600;
        font-family: monospace;
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        padding: 4px 10px;
        border-radius: 6px;
    }
    
    .card-image-wrapper {
        width: 100%;
        height: 190px;
        border-radius: 10px;
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        overflow: hidden;
        margin-bottom: 18px;
        position: relative;
    }
    .card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: zoom-in;
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .premium-logbook-card:hover .card-image {
        transform: scale(1.04);
    }
    .card-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .desc-text {
        margin: 0 0 20px 0;
        font-size: 13.5px;
        color: var(--text-secondary);
        line-height: 1.6;
        height: 44px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .card-actions {
        border-top: 1px solid var(--border-border);
        padding-top: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    @media (max-width: 480px) {
        .card-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .card-actions > button, 
        .card-actions > div {
            width: 100% !important;
        }
        .card-actions > div {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }
        .card-actions form, 
        .card-actions form button {
            width: 100% !important;
        }
    }
    
    .btn-action-premium {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-action-detail {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-border);
    }
    .btn-action-detail:hover {
        background: var(--bg-card-hover);
        color: var(--text-foreground);
        border-color: var(--border-hover);
    }
    .btn-action-approve {
        background: var(--text-foreground);
        color: var(--bg-background);
        border: none;
    }
    .btn-action-approve:hover {
        opacity: 0.9;
        box-shadow: var(--shadow-subtle);
    }
    .btn-action-reject {
        background: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border-border);
    }
    .btn-action-reject:hover {
        background: var(--accent-danger-glow);
        color: var(--accent-danger);
        border-color: var(--accent-danger);
    }
    
    /* Modals & Overlays */
    .overlay-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        backdrop-filter: blur(12px);
        z-index: 100;
        animation: fadeIn 0.25s ease;
    }
    .detail-dialog-panel {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--bg-card);
        border: 1px solid var(--border-border);
        border-radius: 20px;
        width: 95%;
        max-width: 720px;
        max-height: 90vh;
        z-index: 110;
        flex-direction: column;
        overflow: hidden;
        box-shadow: var(--shadow-card);
    }
    @media (max-width: 640px) {
        .detail-dialog-panel {
            width: 100% !important;
            height: 100% !important;
            max-height: 100vh !important;
            border-radius: 0 !important;
            top: 0 !important;
            left: 0 !important;
            transform: none !important;
        }
    }
    
    .modal-section-title {
        font-size: 10px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 800;
        margin: 0 0 14px 0;
        border-bottom: 1px solid var(--border-border);
        padding-bottom: 8px;
    }
    .modal-grid-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .modal-detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .modal-detail-label {
        font-size: 10.5px;
        color: var(--text-muted);
    }
    .modal-detail-value {
        font-size: 13px;
        color: var(--text-foreground);
        font-weight: 600;
    }
    .modal-detail-value.mono {
        font-family: monospace;
        font-size: 12px;
        color: var(--text-secondary);
    }
    
    [data-theme="light"] .sig-image {
        filter: invert(1) brightness(0.9) !important;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

{{-- Unified Filter Form --}}
<div class="sticky-filter-panel">
    <form id="logbookFilterForm" method="GET" action="/admin/logbook" style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        {{-- Month Filter --}}
        <div class="filter-group">
            <span class="filter-label">Bulan</span>
            <select name="month" onchange="document.getElementById('logbookFilterForm').submit()" class="filter-select">
                @php
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $currentMonth = request('month', now()->month);
                @endphp
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $currentMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Year Filter --}}
        <div class="filter-group">
            <span class="filter-label">Tahun</span>
            <select name="year" onchange="document.getElementById('logbookFilterForm').submit()" class="filter-select">
                @php
                    $currentYear = request('year', now()->year);
                @endphp
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        {{-- Status Filter --}}
        <div class="filter-group">
            <span class="filter-label">Status</span>
            <select name="status" onchange="document.getElementById('logbookFilterForm').submit()" class="filter-select">
                @php
                    $statuses = [
                        'all' => 'Semua Status',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ];
                    $currentStatus = request('status', 'all');
                @endphp
                @foreach($statuses as $val => $lbl)
                    <option value="{{ $val }}" {{ $currentStatus == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Export Button --}}
    <a href="/admin/export/excel?month={{ request('month', now()->month) }}&year={{ request('year', now()->year) }}" class="btn-export-excel">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Ekspor Excel
    </a>
</div>

{{-- Cards Grid Layout --}}
<div class="logbook-grid">
    @forelse($logbooks as $lb)
        @php
            $initials = collect(explode(' ', $lb->user->name ?? 'User'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
        @endphp
        <div class="premium-logbook-card">
            <div>
                {{-- Header --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; gap:12px;">
                    <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                        <div class="avatar-initials">
                            {{ $initials }}
                        </div>
                        <div style="min-width:0;">
                            <h4 style="margin:0; font-size:14px; font-weight:700; color:var(--text-foreground); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">{{ $lb->user->name ?? 'User Terhapus' }}</h4>
                            <p style="margin:3px 0 0; font-size:11px; color:var(--text-secondary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $lb->user->asal_instansi ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <span class="date-badge">
                        {{ $lb->tanggal->format('d M Y') }}
                    </span>
                </div>

                {{-- Activity Image --}}
                <div class="card-image-wrapper">
                    @if($lb->foto_kegiatan)
                        <img src="{{ asset('storage/'.$lb->foto_kegiatan) }}" onclick="openZoomModal(this.src)" class="card-image" alt="Foto Kegiatan">
                    @else
                        <div class="card-image-placeholder">Tidak Ada Foto</div>
                    @endif
                    
                    {{-- Status Badge on Image --}}
                    <div style="position:absolute; top:12px; right:12px;">
                        @if($lb->status == 'pending')
                            <span class="badge-warning" style="border-radius:20px; font-size:9.5px; text-transform:uppercase; letter-spacing:0.5px;">Pending</span>
                        @elseif($lb->status == 'approved')
                            <span class="badge-success" style="border-radius:20px; font-size:9.5px; text-transform:uppercase; letter-spacing:0.5px;">Approved</span>
                        @else
                            <span class="badge-danger" style="border-radius:20px; font-size:9.5px; text-transform:uppercase; letter-spacing:0.5px;">Rejected</span>
                        @endif
                    </div>
                </div>

                {{-- Preview Description (Max 2 lines) --}}
                <p class="desc-text">
                    {{ $lb->deskripsi }}
                </p>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="card-actions">
                <button onclick="openDetailModal({{ json_encode($lb->load('user', 'admin', 'attendance')) }})" class="btn-action-premium btn-action-detail">Detail</button>
                
                @if($lb->status === 'pending')
                    <div style="display:flex; gap:8px;">
                        <button onclick="openSignModal({{ $lb->id }})" class="btn-action-premium btn-action-approve">Approve</button>
                        <form method="POST" action="/admin/logbook/{{ $lb->id }}/reject" onsubmit="return confirm('Reject logbook ini?')">
                            @csrf
                            <button type="submit" class="btn-action-premium btn-action-reject">Reject</button>
                        </form>
                    </div>
                @elseif($lb->tanda_tangan_admin)
                    <div style="display:flex; align-items:center; gap:8px; cursor:zoom-in;" onclick="openZoomModal('{{ $lb->tanda_tangan_admin }}', true)">
                        <span style="font-size:9px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">TTD Mentor:</span>
                        <img src="{{ $lb->tanda_tangan_admin }}" class="sig-image" style="height:26px; max-width:90px; object-fit:contain; filter:brightness(0.95);" alt="TTD">
                    </div>
                @else
                    <span style="font-size:11.5px; color:var(--text-muted); font-style:italic;">Diproses</span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1; background:var(--bg-card); border:1px solid var(--border-border); border-radius:16px; padding:64px; text-align:center; color:var(--text-muted);">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px; color:var(--border-hover);"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            <h3 style="font-size:16px; font-weight:700; color:var(--text-foreground); margin:0;">Tidak ada logbook ditemukan</h3>
            <p style="font-size:13px; color:var(--text-secondary); margin:5px 0 0 0;">Coba ubah filter pencarian bulan, tahun, atau status di atas.</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div style="margin-top:20px;">
    {{ $logbooks->appends(request()->query())->links() }}
</div>


{{-- PREMIUM DETAIL MODAL --}}
<div id="detailOverlay" onclick="closeDetailModal()" class="overlay-backdrop"></div>
<div id="detailModal" class="detail-dialog-panel">
    <!-- Modal Header -->
    <div style="padding:24px 28px; border-bottom:1px solid var(--border-border); display:flex; align-items:center; justify-content:space-between; background:var(--bg-input);">
        <div>
            <h3 style="margin:0; font-size:16px; font-weight:700; color:var(--text-foreground); letter-spacing:-0.2px;">Verifikasi Aktivitas Harian</h3>
            <p style="margin:4px 0 0 0; font-size:12px; color:var(--text-muted);">Review data kehadiran dan pelaporan tugas magang</p>
        </div>
        <button onclick="closeDetailModal()" style="background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:24px; line-height:1; outline:none; transition:color .2s;" onmouseover="this.style.color='var(--text-foreground)'" onmouseout="this.style.color='var(--text-muted)'">&times;</button>
    </div>

    <!-- Modal Body (Scrollable) -->
    <div style="overflow-y:auto; padding:28px; display:flex; flex-direction:column; gap:24px;">
        
        <!-- SECTION 1: USER INFO -->
        <div>
            <h4 class="modal-section-title">Informasi Mahasiswa / Siswa</h4>
            <div class="modal-grid-details">
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Nama Lengkap</span>
                    <strong id="detName" class="modal-detail-value">-</strong>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Instansi Pendidikan</span>
                    <strong id="detInstansi" class="modal-detail-value">-</strong>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Username</span>
                    <strong id="detUsername" class="modal-detail-value mono">-</strong>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Nomor Telepon</span>
                    <strong id="detPhone" class="modal-detail-value mono">-</strong>
                </div>
            </div>
        </div>

        <!-- SECTION 2: ATTENDANCE INFO -->
        <div>
            <h4 class="modal-section-title">Informasi Absensi Hari Ini</h4>
            <div class="modal-grid-details">
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Tanggal Kerja</span>
                    <strong id="detDate" class="modal-detail-value mono">-</strong>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Jam Clock In</span>
                    <strong id="detIn" class="modal-detail-value mono" style="color:var(--accent-success);">-</strong>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Jam Clock Out</span>
                    <strong id="detOut" class="modal-detail-value mono" style="color:var(--accent-warning);">-</strong>
                </div>
                <div class="modal-detail-item">
                    <span class="modal-detail-label">Kondisi Kehadiran</span>
                    <span id="detAttStatus">-</span>
                </div>
            </div>
            
            <div style="background:var(--bg-input); border:1px solid var(--border-border); border-radius:10px; padding:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <span style="font-size:10px; color:var(--text-muted); display:block; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">Lokasi GPS Absensi</span>
                    <strong id="detGPS" style="font-size:12px; color:var(--text-secondary); font-family:monospace;">-</strong>
                </div>
                <a id="detMapsLink" target="_blank" href="#" style="background:var(--bg-card); border:1px solid var(--border-border); color:var(--text-secondary); text-decoration:none; padding:7px 14px; border-radius:8px; font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:6px; transition:all .2s;" onmouseover="this.style.background='var(--bg-card-hover)';this.style.color='var(--text-foreground)';this.style.borderColor='var(--border-hover)'" onmouseout="this.style.background='var(--bg-card)';this.style.color='var(--text-secondary)';this.style.borderColor='var(--border-border)'">
                    📍 Buka Google Maps
                </a>
            </div>
        </div>

        <!-- SECTION 3: ACTIVITY DETAIL -->
        <div>
            <h4 class="modal-section-title">Deskripsi & Bukti Kegiatan</h4>
            <div style="font-size:13.5px; color:var(--text-foreground); line-height:1.6; background:var(--bg-input); border:1px solid var(--border-border); padding:18px; border-radius:12px; margin-bottom:20px; white-space:pre-wrap; max-height:160px; overflow-y:auto;" id="detDesc">-</div>
            
            <div style="display:flex; flex-wrap:wrap; gap:24px;">
                {{-- Foto Kegiatan --}}
                <div style="flex:1; min-width:200px;">
                    <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; font-weight:700; display:block; margin-bottom:8px;">Foto Kegiatan (Klik Zoom)</span>
                    <div style="width:100%; height:160px; border-radius:12px; border:1px solid var(--border-border); background:var(--bg-input); overflow:hidden; position:relative;">
                        <img id="detPhoto" onclick="openZoomModal(this.src)" class="card-image" src="" style="width:100%; height:100%; object-fit:cover; display:none;">
                        <div id="detPhotoPlaceholder" class="card-image-placeholder">Tidak Ada Foto</div>
                    </div>
                </div>
                {{-- TTD Mentor --}}
                <div style="flex:1; min-width:180px;">
                    <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; font-weight:700; display:block; margin-bottom:8px;">Tanda Tangan Mentor (Klik Zoom)</span>
                    <div style="width:100%; height:160px; border-radius:12px; border:1px solid var(--border-border); background:var(--bg-input); overflow:hidden; display:flex; align-items:center; justify-content:center; position:relative;">
                        <img id="detSignature" onclick="openZoomModal(this.src, true)" class="card-image sig-image" src="" style="max-height:140px; max-width:90%; object-fit:contain; filter:brightness(0.95); display:none;">
                        <div id="detSignaturePlaceholder" class="card-image-placeholder">Belum Di-approve</div>
                    </div>
                </div>
                {{-- TTD Pembimbing --}}
                <div style="flex:1; min-width:180px;">
                    <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; font-weight:700; display:block; margin-bottom:8px;">Tanda Tangan Pembimbing (Klik Zoom)</span>
                    <div style="width:100%; height:160px; border-radius:12px; border:1px solid var(--border-border); background:var(--bg-input); overflow:hidden; display:flex; align-items:center; justify-content:center; position:relative;">
                        <img id="detSignaturePembimbing" onclick="openZoomModal(this.src, true)" class="card-image sig-image" src="" style="max-height:140px; max-width:90%; object-fit:contain; filter:brightness(0.95); display:none;">
                        <div id="detSignaturePembimbingPlaceholder" class="card-image-placeholder">Belum Di-approve</div>
                    </div>
                </div>
            </div>
            
            {{-- Komentar Pembimbing --}}
            <div id="detCommentSection" style="margin-top:20px; display:none;">
                <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; font-weight:700; display:block; margin-bottom:8px;">Komentar Pembimbing</span>
                <div style="font-size:13px; color:var(--text-secondary); font-style:italic; line-height:1.5; background:var(--bg-input); border:1px solid var(--border-border); padding:14px 18px; border-radius:12px;" id="detComment">-</div>
            </div>
        </div>

        <!-- SECTION 4: APPROVAL STATUS -->
        <div style="border-top:1px solid var(--border-border); padding-top:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; font-weight:700; display:block; margin-bottom:6px;">Status Logbook</span>
                <span id="detStatusBadge">-</span>
            </div>
            <div id="modalActions" style="display:flex; gap:8px;">
                {{-- Populated dynamically --}}
            </div>
        </div>
    </div>
</div>


{{-- FULLSCREEN ZOOM OVERLAY --}}
<div id="zoomOverlay" onclick="closeZoomModal()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.95); backdrop-filter:blur(24px); z-index:200; align-items:center; justify-content:center; cursor:zoom-out; animation:fadeIn 0.2s ease;">
    <img id="zoomImage" src="" style="max-width:90%; max-height:85vh; object-fit:contain; border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,0.8); border:1px solid var(--border-border);">
    <button onclick="closeZoomModal()" style="position:absolute; top:24px; right:28px; background:none; border:none; color:#777; cursor:pointer; font-size:32px; line-height:1; outline:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#777'">&times;</button>
</div>


{{-- Signature Drawing Modal (Approvals) --}}
<div id="signOverlay" onclick="closeSignModal()" style="position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:150; display:none; animation:fadeIn 0.2s ease;"></div>
<div id="signModal" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:var(--bg-card); border:1px solid var(--border-border); border-radius:20px; padding:28px; z-index:160; width:90%; max-width:460px; display:none; box-shadow:var(--shadow-card);">
    <h3 style="margin:0 0 6px; font-size:15px; font-weight:700; color:var(--text-foreground); letter-spacing:-0.2px;">Tanda Tangan Digital Mentor</h3>
    <p style="font-size:12px; color:var(--text-muted); margin:0 0 18px;">Tuliskan tanda tangan Anda pada bidang hitam di bawah ini.</p>
    <canvas id="signCanvas" width="400" height="200" style="width:100%; border:1px solid var(--border-border); border-radius:10px; background:#0a0a0a; cursor:crosshair;"></canvas>
    <div style="display:flex; gap:10px; margin-top:18px;">
        <button onclick="clearSign()" style="padding:10px 16px; background:transparent; border:1px solid var(--border-border); border-radius:8px; color:var(--text-secondary); cursor:pointer; font-size:12.5px; font-weight:600; transition:all .2s;" onmouseover="this.style.borderColor='var(--border-hover)';this.style.color='var(--text-foreground)'" onmouseout="this.style.borderColor='var(--border-border)';this.style.color='var(--text-secondary)'">Bersihkan</button>
        <button onclick="submitSign()" style="flex:1; padding:10px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size:12.5px; transition:all .2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Setujui & Simpan</button>
        <button onclick="closeSignModal()" style="padding:10px 16px; background:transparent; border:1px solid var(--border-border); border-radius:8px; color:var(--text-secondary); cursor:pointer; font-size:12.5px; font-weight:600; transition:all .2s;" onmouseover="this.style.borderColor='var(--border-hover)';this.style.color='var(--text-foreground)'" onmouseout="this.style.borderColor='var(--border-border)';this.style.color='var(--text-secondary)'">Batal</button>
    </div>
    <form id="approveForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="tanda_tangan" id="signDataInput">
    </form>
</div>
@endsection

@push('scripts')
<script>
    // ── Signature Drawing Board ──
    let signCtx, signing = false, currentLogbookId = null;
    const canvas = document.getElementById('signCanvas');
    if (canvas) {
        signCtx = canvas.getContext('2d');
        signCtx.strokeStyle = '#ffffff'; signCtx.lineWidth = 3.5; signCtx.lineCap = 'round';
        canvas.addEventListener('mousedown', e => { signing = true; signCtx.beginPath(); signCtx.moveTo(e.offsetX, e.offsetY); });
        canvas.addEventListener('mousemove', e => { if (signing) { signCtx.lineTo(e.offsetX, e.offsetY); signCtx.stroke(); } });
        canvas.addEventListener('mouseup', () => signing = false);
        canvas.addEventListener('mouseleave', () => signing = false);
        // Touch support
        canvas.addEventListener('touchstart', e => { e.preventDefault(); signing = true; const r = canvas.getBoundingClientRect(); signCtx.beginPath(); signCtx.moveTo(e.touches[0].clientX - r.left, e.touches[0].clientY - r.top); });
        canvas.addEventListener('touchmove', e => { e.preventDefault(); if (signing) { const r = canvas.getBoundingClientRect(); signCtx.lineTo(e.touches[0].clientX - r.left, e.touches[0].clientY - r.top); signCtx.stroke(); } });
        canvas.addEventListener('touchend', () => signing = false);
    }

    function openSignModal(id) {
        if (confirm("Yakin ingin menyetujui logbook ini? Anda perlu memberikan tanda tangan mentor.")) {
            currentLogbookId = id;
            document.getElementById('signOverlay').style.display = 'block';
            document.getElementById('signModal').style.display = 'block';
            clearSign();
        }
    }

    function closeSignModal() {
        document.getElementById('signOverlay').style.display = 'none';
        document.getElementById('signModal').style.display = 'none';
    }

    function clearSign() { 
        signCtx.clearRect(0, 0, canvas.width, canvas.height); 
    }

    function submitSign() {
        // Check if blank
        const blank = document.createElement('canvas');
        blank.width = canvas.width;
        blank.height = canvas.height;
        if (canvas.toDataURL() === blank.toDataURL()) {
            alert('Silakan coretkan tanda tangan Anda terlebih dahulu.');
            return;
        }

        const data = canvas.toDataURL('image/png');
        document.getElementById('signDataInput').value = data;
        const form = document.getElementById('approveForm');
        form.action = '/admin/logbook/' + currentLogbookId + '/approve';
        form.submit();
    }

    // ── Fullscreen Zoom Image ──
    function openZoomModal(src, isSignature = false) {
        if (!src) return;
        const zoomImg = document.getElementById('zoomImage');
        zoomImg.src = src;
        if (isSignature) {
            zoomImg.classList.add('sig-image');
        } else {
            zoomImg.classList.remove('sig-image');
        }
        document.getElementById('zoomOverlay').style.display = 'flex';
    }

    function closeZoomModal() {
        document.getElementById('zoomOverlay').style.display = 'none';
    }

    // ── Detail Modal Populator ──
    function openDetailModal(lb) {
        // User Info
        document.getElementById('detName').textContent = lb.user ? lb.user.name : 'User Terhapus';
        document.getElementById('detUsername').textContent = lb.user ? '@' + lb.user.username : '-';
        document.getElementById('detInstansi').textContent = lb.user ? (lb.user.asal_instansi || '-') : '-';
        document.getElementById('detPhone').textContent = lb.user ? (lb.user.no_telp || '-') : '-';
        
        // Attendance Info
        const dateStr = formatDate(lb.tanggal);
        document.getElementById('detDate').textContent = dateStr;
        
        const att = lb.attendance;
        if (att) {
            document.getElementById('detIn').textContent = formatTime(att.jam_masuk) + ' WIB';
            document.getElementById('detOut').textContent = att.jam_pulang ? (formatTime(att.jam_pulang) + ' WIB') : 'Belum Pulang';
            document.getElementById('detAttStatus').innerHTML = renderAttBadge(att.status);
            document.getElementById('detGPS').textContent = att.latitude + ', ' + att.longitude;
            document.getElementById('detMapsLink').href = `https://www.google.com/maps/search/?api=1&query=${att.latitude},${att.longitude}`;
            document.getElementById('detMapsLink').style.display = 'inline-flex';
        } else {
            document.getElementById('detIn').textContent = '-';
            document.getElementById('detOut').textContent = '-';
            document.getElementById('detAttStatus').innerHTML = '<span style="padding:4px 10px; border-radius:6px; font-size:10px; font-weight:700; background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.25);">Alpa / Tidak Absen</span>';
            document.getElementById('detGPS').textContent = 'Tidak ada koordinat';
            document.getElementById('detMapsLink').style.display = 'none';
        }

        // Activity Detail
        document.getElementById('detDesc').textContent = lb.deskripsi;
        
        const photo = document.getElementById('detPhoto');
        const photoPlaceholder = document.getElementById('detPhotoPlaceholder');
        if (lb.foto_kegiatan) {
            photo.src = '/storage/' + lb.foto_kegiatan;
            photo.style.display = 'block';
            photoPlaceholder.style.display = 'none';
        } else {
            photo.style.display = 'none';
            photoPlaceholder.style.display = 'flex';
        }

        // TTD Admin / Mentor
        const sig = document.getElementById('detSignature');
        const sigPlaceholder = document.getElementById('detSignaturePlaceholder');
        if (lb.tanda_tangan_admin) {
            sig.src = lb.tanda_tangan_admin;
            sig.style.display = 'block';
            sigPlaceholder.style.display = 'none';
        } else {
            sig.style.display = 'none';
            sigPlaceholder.style.display = 'flex';
            sigPlaceholder.textContent = lb.status === 'approved' ? 'Approved (Tanpa TTD)' : (lb.status === 'rejected' ? 'Rejected' : 'Belum Di-approve');
        }

        // TTD Pembimbing
        const sigPem = document.getElementById('detSignaturePembimbing');
        const sigPemPlaceholder = document.getElementById('detSignaturePembimbingPlaceholder');
        if (lb.tanda_tangan_pembimbing) {
            sigPem.src = lb.tanda_tangan_pembimbing;
            sigPem.style.display = 'block';
            sigPemPlaceholder.style.display = 'none';
        } else {
            sigPem.style.display = 'none';
            sigPemPlaceholder.style.display = 'flex';
            sigPemPlaceholder.textContent = lb.status === 'approved' ? 'Approved (Tanpa TTD)' : (lb.status === 'rejected' ? 'Rejected' : 'Belum Di-approve');
        }

        // Komentar Pembimbing
        const commentSection = document.getElementById('detCommentSection');
        const commentDiv = document.getElementById('detComment');
        if (lb.komentar_pembimbing) {
            commentDiv.textContent = lb.komentar_pembimbing;
            commentSection.style.display = 'block';
        } else {
            commentDiv.textContent = '-';
            commentSection.style.display = 'none';
        }

        // Logbook Status Badge
        document.getElementById('detStatusBadge').innerHTML = renderStatusBadge(lb.status);

        // Actions inside modal
        const actionBlock = document.getElementById('modalActions');
        if (lb.status === 'pending') {
            actionBlock.innerHTML = `
                <button onclick="triggerModalApprove(${lb.id})" style="padding:8px 16px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size:12px; transition:all .2s;">Setujui Logbook</button>
                <form method="POST" action="/admin/logbook/${lb.id}/reject" onsubmit="return confirm('Reject logbook ini?')">
                    @csrf
                    <button type="submit" style="padding:8px 16px; background:transparent; color:var(--text-secondary); border:1px solid var(--border-border); border-radius:8px; cursor:pointer; font-weight:600; font-size:12px; transition:all .2s;" onmouseover="this.style.color='var(--accent-danger)';this.style.borderColor='var(--accent-danger)'" onmouseout="this.style.color='var(--text-secondary)';this.style.borderColor='var(--border-border)'">Reject</button>
                </form>
            `;
        } else {
            const adminName = lb.admin ? lb.admin.name : 'System';
            actionBlock.innerHTML = `<span style="font-size:12px; color:var(--text-muted); font-style:italic;">Diproses oleh ${adminName}</span>`;
        }

        document.getElementById('detailOverlay').style.display = 'block';
        document.getElementById('detailModal').style.display = 'flex';
    }

    function triggerModalApprove(id) {
        closeDetailModal();
        openSignModal(id);
    }

    function closeDetailModal() {
        document.getElementById('detailOverlay').style.display = 'none';
        document.getElementById('detailModal').style.display = 'none';
    }

    // ── Helper JS Parsers ──
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

    function formatTime(timeStr) {
        if (!timeStr) return '-';
        const parts = timeStr.split(':');
        return parts[0] + ':' + parts[1];
    }

    function renderAttBadge(status) {
        if (status === 'tepat_waktu') {
            return '<span class="badge-success">Tepat Waktu</span>';
        } else if (status === 'terlambat') {
            return '<span class="badge-warning">Terlambat</span>';
        } else if (status === 'izin') {
            return '<span class="badge-info">Izin</span>';
        } else if (status === 'sakit') {
            return '<span class="badge-info">Sakit</span>';
        } else if (status === 'libur_nasional') {
            return '<span style="background: var(--bg-input); color: var(--text-secondary); border: 1px solid var(--border-border); padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Libur Nasional</span>';
        } else {
            return '<span class="badge-danger">Tidak Hadir</span>';
        }
    }

    function renderStatusBadge(status) {
        if (status === 'pending') {
            return '<span class="badge-warning" style="border-radius:20px; font-size:9.5px; text-transform:uppercase; letter-spacing:0.5px;">Pending</span>';
        } else if (status === 'approved') {
            return '<span class="badge-success" style="border-radius:20px; font-size:9.5px; text-transform:uppercase; letter-spacing:0.5px;">Approved</span>';
        } else {
            return '<span class="badge-danger" style="border-radius:20px; font-size:9.5px; text-transform:uppercase; letter-spacing:0.5px;">Rejected</span>';
        }
    }
</script>
@endpush

