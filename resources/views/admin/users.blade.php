@extends('layouts.app')
@section('title', 'Kelola User')
@section('subtitle', 'Manajemen akun pengguna dan monitoring status magang')

@section('content')
<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    @media (min-width: 576px) {
        .stats-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
    }
    @media (min-width: 1200px) {
        .stats-container {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .stats-card {
        background: var(--bg-card);
        border: 1px solid var(--border-border);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-subtle);
    }
    .stats-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 14px;
        padding: 1px;
        background: linear-gradient(180deg, var(--border-glass), transparent);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .stats-title {
        font-size: 10px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }
    .stats-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-foreground);
        font-family: monospace;
    }
    .stats-desc {
        font-size: 11px;
        color: var(--text-secondary);
    }

    .search-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 32px;
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 16px;
        align-items: center;
        box-shadow: var(--shadow-subtle);
    }
    @media (max-width: 992px) {
        .search-panel {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .search-panel {
            grid-template-columns: 1fr;
        }
    }
    .search-input-wrapper {
        position: relative;
    }
    .search-input-wrapper svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        transition: color 0.2s;
    }
    .custom-input {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        color: var(--text-foreground);
        padding: 10px 14px 10px 42px;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        transition: all 0.2s ease;
    }
    .custom-input:focus {
        border-color: var(--accent-primary) !important;
        box-shadow: 0 0 0 3px var(--accent-primary-glow) !important;
    }
    .custom-select-wrapper {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .custom-select-label {
        font-size: 10px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }
    .custom-select {
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        color: var(--text-secondary);
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        outline: none;
        width: 100%;
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23888888'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        padding-right: 36px;
    }
    .custom-select:focus {
        border-color: var(--accent-primary) !important;
        box-shadow: 0 0 0 3px var(--accent-primary-glow) !important;
    }
    
    .user-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    .premium-user-card {
        background: var(--bg-card);
        border: 1px solid var(--border-border);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: var(--shadow-subtle);
    }
    .premium-user-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 16px;
        padding: 1px;
        background: linear-gradient(180deg, var(--border-glass), transparent);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .premium-user-card:hover {
        transform: translateY(-4px);
        border-color: var(--border-hover);
        box-shadow: var(--shadow-card);
    }
    
    .index-badge {
        position: absolute;
        top: 18px;
        right: 18px;
        font-family: monospace;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        padding: 2px 8px;
        border-radius: 6px;
    }
    
    .user-avatar-circle {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-foreground);
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
        position: relative;
    }
    .user-avatar-circle::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 12px;
        border: 1px solid var(--border-glass);
        pointer-events: none;
    }
    
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        border-top: 1px solid var(--border-border);
        padding-top: 18px;
        margin-bottom: 20px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .info-label {
        font-size: 9px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
    }
    .info-value {
        font-size: 12.5px;
        color: var(--text-foreground);
        font-weight: 500;
    }
    .info-value.mono {
        font-family: monospace;
        font-size: 11.5px;
        color: var(--text-secondary);
    }
    
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .role-badge-admin {
        background: var(--bg-input);
        color: var(--text-foreground);
        border: 1px solid var(--border-border);
    }
    .role-badge-user {
        background: var(--bg-input);
        color: var(--text-secondary);
        border: 1px solid var(--border-border);
    }
    
    .btn-delete-account {
        width: 100%;
        padding: 9px;
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-border);
        border-radius: 8px;
        cursor: pointer;
        font-size: 11.5px;
        font-weight: 600;
        transition: all 0.2s ease;
        text-align: center;
    }
    .btn-delete-account:hover {
        background: var(--accent-danger-glow);
        color: var(--accent-danger);
        border-color: var(--accent-danger);
    }
    .active-account-badge {
        width: 100%;
        display: block;
        text-align: center;
        font-size: 11.5px;
        color: var(--text-muted);
        font-style: italic;
        padding: 9px;
        background: var(--bg-input);
        border: 1px solid var(--border-border);
        border-radius: 8px;
    }
</style>

{{-- SaaS Stats Overview --}}
<div class="stats-container">
    <div class="stats-card">
        <span class="stats-title">Total Anggota</span>
        <span class="stats-value">{{ $totalUsers }}</span>
        <span class="stats-desc">Pengguna terdaftar aktif</span>
    </div>
    <div class="stats-card">
        <span class="stats-title">Mahasiswa Magang</span>
        <span class="stats-value">{{ $totalMahasiswa }}</span>
        <span class="stats-desc">Asal Universitas / Institut</span>
    </div>
    <div class="stats-card">
        <span class="stats-title">Siswa SMK / SMA</span>
        <span class="stats-value">{{ $totalSiswa }}</span>
        <span class="stats-desc">Pendidikan menengah</span>
    </div>
    <div class="stats-card">
        <span class="stats-title">Mentor & Admin</span>
        <span class="stats-value">{{ $totalAdmin }}</span>
        <span class="stats-desc">Hak akses administrator</span>
    </div>
</div>

{{-- Unified Search & Filter Panel --}}
<form id="searchForm" method="GET" action="/admin/users" class="search-panel">
    {{-- Search Input --}}
    <div class="custom-select-wrapper" style="grid-column: span 1;">
        <span class="custom-select-label">Cari Pengguna</span>
        <div class="search-input-wrapper">
            <input type="text" name="search" id="userSearchInput" value="{{ $search }}" placeholder="Nama, username, instansi..." class="custom-input">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
    </div>

    {{-- Instansi Filter --}}
    <div class="custom-select-wrapper">
        <span class="custom-select-label">Instansi</span>
        <select name="instansi" onchange="document.getElementById('searchForm').submit()" class="custom-select">
            <option value="">Semua Instansi</option>
            @foreach($instansiList as $item)
                <option value="{{ $item }}" {{ $instansi === $item ? 'selected' : '' }}>{{ $item }}</option>
            @endforeach
        </select>
    </div>

    {{-- Role Filter --}}
    <div class="custom-select-wrapper">
        <span class="custom-select-label">Role</span>
        <select name="role" onchange="document.getElementById('searchForm').submit()" class="custom-select">
            <option value="all" {{ $role === 'all' ? 'selected' : '' }}>Semua Role</option>
            <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User</option>
            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
    </div>

    {{-- Status Filter --}}
    <div class="custom-select-wrapper">
        <span class="custom-select-label">Status Akun</span>
        <select name="status" onchange="document.getElementById('searchForm').submit()" class="custom-select">
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Arsip</option>
        </select>
    </div>
</form>

{{-- Grid Layout for Users --}}
<div class="user-grid">
    @forelse($users as $idx => $u)
        @php
            $initials = collect(explode(' ', $u->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
            $rowNumber = $users->firstItem() + $idx;
        @endphp
        <div class="premium-user-card" style="{{ $u->trashed() ? 'opacity: 0.7; border-color: var(--accent-danger);' : '' }}">
            {{-- Index Number Badge --}}
            <span class="index-badge">#{{ sprintf('%02d', $rowNumber) }}</span>

            <div>
                {{-- Header --}}
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                    <div class="user-avatar-circle">
                        {{ $initials }}
                    </div>
                    <div style="min-width:0;">
                        <h3 style="margin:0; font-size:15px; font-weight:700; color:var(--text-foreground); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.2px;">{{ $u->name }}</h3>
                        <p style="margin:4px 0 0; font-size:11.5px; color:var(--text-muted); font-family:monospace;">{{ '@' . $u->username }}</p>
                    </div>
                </div>

                {{-- Info Section --}}
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Asal Instansi</span>
                        <span class="info-value">{{ $u->asal_instansi ?: '-' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Kontak Resmi</span>
                        <span class="info-value mono">{{ $u->email }}</span>
                        @if($u->no_telp)
                            <span class="info-value mono" style="margin-top:2px;">{{ $u->no_telp }}</span>
                        @endif
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                        <div class="info-item">
                            <span class="info-label">Status Keanggotaan</span>
                            <div style="display:flex; gap:6px; align-items:center; margin-top:4px;">
                                @if($u->role === 'admin')
                                    <span class="role-badge role-badge-admin">Mentor</span>
                                @else
                                    <span class="role-badge role-badge-user">Siswa</span>
                                @endif
                                
                                @if($u->trashed())
                                    <span class="role-badge" style="background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25);">Arsip</span>
                                @else
                                    <span class="role-badge" style="background:rgba(34,197,94,0.12); color:#22c55e; border:1px solid rgba(34,197,94,0.25);">Aktif</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item" style="text-align:right;">
                            <span class="info-label">Terdaftar</span>
                            <span class="info-value" style="font-size:11.5px; color:var(--text-muted);">{{ $u->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Action --}}
            <div style="margin-top:auto; padding-top:12px;">
                @if($u->id !== auth()->id())
                    @if($u->trashed())
                        <div style="display:flex; gap:8px;">
                            <form method="POST" action="/admin/users/{{ $u->id }}/restore" style="flex:1;">
                                @csrf
                                <button type="submit" style="width:100%; padding:9px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; font-size:11.5px; font-weight:700; cursor:pointer; transition:all 0.2s ease;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                    Aktifkan
                                </button>
                            </form>
                            <form method="POST" action="/admin/users/{{ $u->id }}/force" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen {{ $u->name }}? Tindakan ini tidak bisa dibatalkan.')" style="flex:1;">
                                @csrf @method('DELETE')
                                <button type="submit" style="width:100%; padding:9px; background:transparent; color:var(--accent-danger); border:1px solid var(--border-border); border-radius:8px; font-size:11.5px; font-weight:600; cursor:pointer; transition:all 0.2s ease;" onmouseover="this.style.background='var(--accent-danger-glow)';this.style.borderColor='var(--accent-danger)';this.style.color='var(--accent-danger)'" onmouseout="this.style.background='transparent';this.style.borderColor='var(--border-border)';this.style.color='var(--accent-danger)'">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="/admin/users/{{ $u->id }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete-account">
                                Arsipkan Akun
                            </button>
                        </form>
                    @endif
                @else
                    <span class="active-account-badge">Akun Aktif Anda</span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-border); border-radius: 16px; padding: 64px; text-align: center; color: var(--text-muted);">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px; color:var(--border-hover);"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <h3 style="font-size:16px; font-weight:700; color:var(--text-foreground); margin:0;">Tidak ada user ditemukan</h3>
            <p style="font-size:13px; color:var(--text-secondary); margin:5px 0 0 0;">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div style="margin-top:20px;">
    {{ $users->links() }}
</div>

<script>
    // Debounce search submission
    let searchTimer;
    document.getElementById('userSearchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.getElementById('searchForm').submit();
        }, 500);
    });
</script>
@endsection


