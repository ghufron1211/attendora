@extends('layouts.app')
@section('title', 'Tambah Logbook')
@section('subtitle', 'Input kegiatan hari ini')

@section('content')
<div style="max-width:580px;">
    <div style="background:#141414;border:1px solid #1a1a1a;border-radius:12px;padding:24px;">
        <form method="POST" action="/logbook" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:6px;font-weight:500;color:#666;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Tanggal</label>
                <input type="text" value="{{ now()->format('d M Y') }}" disabled style="width:100%;padding:10px 14px;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;color:#555;font-size:13px;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:6px;font-weight:500;color:#666;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Deskripsi Kegiatan <span style="color:#555;">*</span></label>
                <textarea name="deskripsi" rows="5" required class="mono-input" style="width:100%;padding:10px 14px;background:#0a0a0a;border:1px solid #222;border-radius:8px;color:#f5f5f5;font-size:13px;outline:none;box-sizing:border-box;resize:vertical;font-family:inherit;transition:all .2s;" placeholder="Jelaskan kegiatan yang dilakukan hari ini...">{{ old('deskripsi') }}</textarea>
            </div>
            <div style="margin-bottom:22px;">
                <label style="display:block;margin-bottom:6px;font-weight:500;color:#666;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Foto Kegiatan <span style="color:#555;">*</span></label>
                <input type="file" name="foto_kegiatan" accept="image/*" required style="width:100%;padding:10px 14px;background:#0a0a0a;border:1px solid #222;border-radius:8px;color:#999;font-size:13px;box-sizing:border-box;">
                <p style="font-size:11px;color:#444;margin:6px 0 0;">Format: JPG, JPEG, PNG. Maks 5MB.</p>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:1;padding:11px;background:#fff;color:#0a0a0a;border:none;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#e0e0e0'" onmouseout="this.style.background='#fff'">Simpan Logbook</button>
                <a href="/logbook" style="padding:11px 18px;background:transparent;border:1px solid #222;border-radius:8px;color:#666;text-decoration:none;font-weight:500;font-size:13px;display:inline-flex;align-items:center;transition:all .2s;" onmouseover="this.style.borderColor='#444';this.style.color='#ccc'" onmouseout="this.style.borderColor='#222';this.style.color='#666'">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
