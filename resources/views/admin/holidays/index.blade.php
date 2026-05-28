@extends('layouts.app')
@section('title', 'Kelola Hari Libur')
@section('subtitle', 'Daftar tanggal merah dan hari non-kerja nasional')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; background:var(--bg-card); border:1px solid var(--border-border); padding:12px 20px; border-radius:12px;">
    {{-- Filter Form --}}
    <form id="holidayFilterForm" method="GET" action="{{ route('admin.holidays') }}" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Tahun:</span>
            <select name="year" onchange="document.getElementById('holidayFilterForm').submit()" style="background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:6px 12px; border-radius:6px; font-size:12px; cursor:pointer; outline:none;">
                <option value="all" {{ $year === 'all' ? 'selected' : '' }}>Semua</option>
                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px; position:relative;">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama libur..." style="background:var(--bg-input); border:1px solid var(--border-border); color:var(--text-foreground); padding:6px 12px 6px 30px; border-radius:6px; font-size:12px; outline:none; width:180px;">
            <svg width="11" height="11" fill="none" stroke="var(--text-muted)" stroke-width="2.5" viewBox="0 0 24 24" style="position:absolute; left:10px; top:50%; transform:translateY(-50%);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <button type="submit" style="display:none;"></button>
    </form>

    {{-- Add Button --}}
    <button onclick="openHolidayModal()" style="padding:8px 16px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
        + Tambah Hari Libur
    </button>
</div>

<div class="responsive-table" style="background:var(--bg-card); border:1px solid var(--border-border); border-radius:12px; overflow:hidden;">
    <div>
        <table class="mono-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 16px; text-align:left; font-weight:600; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid var(--border-border); background:var(--bg-input); width:60px;">No</th>
                    <th style="padding:12px 16px; text-align:left; font-weight:600; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid var(--border-border); background:var(--bg-input); width:180px;">Tanggal</th>
                    <th style="padding:12px 16px; text-align:left; font-weight:600; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid var(--border-border); background:var(--bg-input);">Nama Hari Libur</th>
                    <th style="padding:12px 16px; text-align:left; font-weight:600; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid var(--border-border); background:var(--bg-input); width:120px;">Tipe</th>
                    <th style="padding:12px 16px; text-align:center; font-weight:600; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid var(--border-border); background:var(--bg-input); width:160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $idx => $h)
                <tr>
                    <td data-label="No" style="padding:11px 16px; border-bottom:1px solid var(--border-border); font-size:13px; color:var(--text-muted);">
                        {{ $holidays->firstItem() + $idx }}
                    </td>
                    <td data-label="Tanggal" style="padding:11px 16px; border-bottom:1px solid var(--border-border); font-size:13px; color:var(--text-foreground); font-weight:500;">
                        {{ $h->tanggal->format('d M Y') }}
                        <span style="font-size:11px; color:var(--text-muted); display:block; font-weight:400;">{{ $h->tanggal->translatedFormat('l') }}</span>
                    </td>
                    <td data-label="Nama Hari Libur" style="padding:11px 16px; border-bottom:1px solid var(--border-border); font-size:13px; color:var(--text-foreground);">
                        {{ $h->nama_libur }}
                    </td>
                    <td data-label="Tipe" style="padding:11px 16px; border-bottom:1px solid var(--border-border);">
                        @if($h->tipe === 'nasional')
                            <span style="padding:3px 8px; border-radius:6px; font-size:10.5px; font-weight:500; background:var(--bg-input); color:var(--text-secondary); border:1px solid var(--border-border);">Nasional</span>
                        @else
                            <span style="padding:3px 8px; border-radius:6px; font-size:10.5px; font-weight:500; background:var(--accent-warning-glow); color:var(--accent-warning); border:1px solid var(--accent-warning-glow);">Khusus</span>
                        @endif
                    </td>
                    <td data-label="Aksi" style="padding:11px 16px; border-bottom:1px solid var(--border-border); text-align:center;">
                        <div style="display:inline-flex; gap:8px;">
                            <button onclick="openHolidayModal({{ json_encode($h) }})" style="padding:5px 12px; background:transparent; color:var(--text-secondary); border:1px solid var(--border-border); border-radius:6px; cursor:pointer; font-size:11px; font-weight:500; transition:all .2s;" onmouseover="this.style.background='var(--bg-card-hover)';this.style.borderColor='var(--border-hover)';this.style.color='var(--text-foreground)'" onmouseout="this.style.background='transparent';this.style.borderColor='var(--border-border)';this.style.color='var(--text-secondary)'">Edit</button>
                            <form method="POST" action="{{ route('admin.holidays.destroy', $h->id) }}" onsubmit="return confirm('Hapus hari libur {{ $h->nama_libur }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:5px 12px; background:transparent; color:var(--text-muted); border:1px solid var(--border-border); border-radius:6px; cursor:pointer; font-size:11px; font-weight:500; transition:all .2s;" onmouseover="this.style.background='var(--accent-danger-glow)';this.style.color='var(--accent-danger)';this.style.borderColor='var(--accent-danger)'" onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)';this.style.borderColor='var(--border-border)'">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:32px; text-align:center; color:var(--text-muted); font-size:13px; font-style:italic;">
                        Belum ada hari libur terdaftar untuk tahun/kriteria ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px;">{{ $holidays->links() }}</div>

{{-- MODAL ADD/EDIT HOLIDAY --}}
<div id="holidayModalOverlay" onclick="closeHolidayModal()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(8px); z-index:100; animation:fadeIn 0.2s;"></div>
<div id="holidayModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:var(--bg-card); border:1px solid var(--border-border); border-radius:16px; width:90%; max-width:400px; z-index:110; flex-direction:column; overflow:hidden; box-shadow:var(--shadow-card);">
    <div style="padding:18px 24px; border-bottom:1px solid var(--border-border); display:flex; align-items:center; justify-content:space-between; background:var(--bg-input);">
        <h3 id="modalTitle" style="margin:0; font-size:14px; font-weight:700; color:var(--text-foreground);">Tambah Hari Libur</h3>
        <button onclick="closeHolidayModal()" style="background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:22px; line-height:1; outline:none;" onmouseover="this.style.color='var(--text-foreground)'" onmouseout="this.style.color='var(--text-muted)'">&times;</button>
    </div>
    
    <form id="holidayForm" method="POST" action="{{ route('admin.holidays.store') }}" style="padding:24px; display:flex; flex-direction:column; gap:16px;">
        @csrf
        <div id="methodContainer"></div>

        <div style="display:flex; flex-direction:column; gap:6px;">
            <label style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Tanggal Hari Libur</label>
            <input type="date" name="tanggal" id="inputTanggal" required style="background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px; color:var(--text-foreground); padding:10px; font-size:13px; outline:none; transition:border 0.2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
        </div>

        <div style="display:flex; flex-direction:column; gap:6px;">
            <label style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Nama Hari Libur</label>
            <input type="text" name="nama_libur" id="inputNamaLibur" placeholder="Contoh: Hari Raya Idul Fitri" required style="background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px; color:var(--text-foreground); padding:10px; font-size:13px; outline:none; transition:border 0.2s;" onfocus="this.style.borderColor='var(--border-hover)'" onblur="this.style.borderColor='var(--border-border)'">
        </div>

        <div style="display:flex; flex-direction:column; gap:6px;">
            <label style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Tipe Libur</label>
            <select name="tipe" id="inputTipe" required style="background:var(--bg-input); border:1px solid var(--border-border); border-radius:8px; color:var(--text-foreground); padding:10px; font-size:13px; outline:none; cursor:pointer;">
                <option value="nasional">Nasional (Tanggal Merah)</option>
                <option value="khusus">Khusus (Cuti Bersama / Khusus)</option>
            </select>
        </div>

        <div style="display:flex; gap:10px; margin-top:8px;">
            <button type="button" onclick="closeHolidayModal()" style="flex:1; padding:10px; background:transparent; border:1px solid var(--border-border); border-radius:8px; color:var(--text-secondary); cursor:pointer; font-size:12.5px; transition:all 0.2s;" onmouseover="this.style.borderColor='var(--border-hover)';this.style.color='var(--text-foreground)'" onmouseout="this.style.borderColor='var(--border-border)';this.style.color='var(--text-secondary)'">Batal</button>
            <button type="submit" id="btnSubmit" style="flex:1; padding:10px; background:var(--text-foreground); color:var(--bg-background); border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:12.5px; transition:all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Simpan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function openHolidayModal(data = null) {
        const overlay = document.getElementById('holidayModalOverlay');
        const modal = document.getElementById('holidayModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('holidayForm');
        const methodContainer = document.getElementById('methodContainer');

        const inputTanggal = document.getElementById('inputTanggal');
        const inputNamaLibur = document.getElementById('inputNamaLibur');
        const inputTipe = document.getElementById('inputTipe');

        if (data) {
            title.textContent = 'Edit Hari Libur';
            form.action = '/admin/holidays/' + data.id;
            methodContainer.innerHTML = '@method("PUT")';
            
            // Format date to YYYY-MM-DD
            const dateObj = new Date(data.tanggal);
            const formattedDate = dateObj.toISOString().split('T')[0];
            inputTanggal.value = formattedDate;
            
            inputNamaLibur.value = data.nama_libur;
            inputTipe.value = data.tipe;
        } else {
            title.textContent = 'Tambah Hari Libur';
            form.action = '{{ route("admin.holidays.store") }}';
            methodContainer.innerHTML = '';
            inputTanggal.value = '';
            inputNamaLibur.value = '';
            inputTipe.value = 'nasional';
        }

        overlay.style.display = 'block';
        modal.style.display = 'flex';
    }

    function closeHolidayModal() {
        document.getElementById('holidayModalOverlay').style.display = 'none';
        document.getElementById('holidayModal').style.display = 'none';
    }
</script>
@endpush
