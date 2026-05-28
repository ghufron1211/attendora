@extends('layouts.app')
@section('title', 'Absensi')
@section('subtitle', 'Clock in & Clock out dengan face recognition')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-7">
    {{-- Webcam & Controls --}}
    <div style="background:var(--bg-card);border:1px solid var(--border-border);border-radius:12px;padding:22px;box-shadow:var(--shadow-subtle);">
        <h3 style="margin:0 0 16px;font-size:14px;font-weight:600;color:var(--text-foreground);letter-spacing:-0.2px;">Face Recognition</h3>
        <div style="text-align:center;">
            <div style="position:relative;display:inline-block;">
                <video id="attVideo" autoplay muted playsinline style="width:100%;max-width:380px;border-radius:8px;display:none;background:#000;"></video>
                <canvas id="attOverlay" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;display:none;"></canvas>
            </div>
            @if($isWeekend || $isHoliday)
            <div id="camPlaceholder" style="padding:40px;color:var(--text-muted);">
                <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;display:block;color:var(--text-muted);"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p style="font-size:13px; font-weight: 700;">
                    @if($isWeekend)
                        Hari ini adalah libur akhir pekan (Sabtu/Minggu).
                    @else
                        Hari ini adalah Libur Nasional: {{ $holidayName }}.
                    @endif
                </p>
                <p style="font-size:11.5px; color:var(--text-muted); margin-top:4px;">Absensi dan pengisian logbook dinonaktifkan.</p>
            </div>
            @else
            <div id="camPlaceholder" style="padding:40px;color:var(--text-muted);">
                <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;display:block;color:var(--text-muted);"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <p style="font-size:13px;">Klik "Buka Kamera" untuk memulai</p>
            </div>
            @endif
            <canvas id="captureCanvas" style="display:none;"></canvas>
        </div>
        <div style="margin-top:14px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
            @if(!$isWeekend && !$isHoliday)
            <button onclick="startAttCamera()" id="btnStartAtt" style="padding:10px 20px;background:var(--text-foreground);color:var(--bg-background);border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;transition:all .2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Buka Kamera</button>
            @endif
            <button onclick="doClockIn()" id="btnClockIn" style="padding:10px 20px;background:var(--text-foreground);color:var(--bg-background);border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;display:none;transition:all .2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">⏰ Clock In</button>
            <button onclick="doClockOut()" id="btnClockOut" style="padding:10px 20px;background:transparent;color:var(--text-secondary);border:1px solid var(--border-border);border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;display:none;transition:all .2s;" onmouseover="this.style.background='var(--bg-card-hover)';this.style.borderColor='var(--border-hover)';this.style.color='var(--text-foreground)'" onmouseout="this.style.background='transparent';this.style.borderColor='var(--border-border)';this.style.color='var(--text-secondary)'">🏠 Clock Out</button>
        </div>
        <div id="attMsg" style="margin-top:12px;text-align:center;font-size:12px;color:var(--text-muted);"></div>
    </div>

    {{-- GPS & Status --}}
    <div>
        <div style="background:var(--bg-card);border:1px solid var(--border-border);border-radius:12px;padding:22px;margin-bottom:16px;box-shadow:var(--shadow-subtle);">
            <h3 style="margin:0 0 14px;font-size:14px;font-weight:600;color:var(--text-foreground);letter-spacing:-0.2px;">📍 Status GPS</h3>
            <div id="gpsStatus" style="font-size:12px;color:var(--text-muted);">
                <p>Mengambil lokasi...</p>
            </div>
            <div id="gpsDetails" style="display:none;font-size:13px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px;">
                    <div><span style="color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Latitude</span><br><strong style="color:var(--text-foreground);" id="gpsLat">-</strong></div>
                    <div><span style="color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Longitude</span><br><strong style="color:var(--text-foreground);" id="gpsLng">-</strong></div>
                    <div><span style="color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Akurasi</span><br><strong style="color:var(--text-foreground);" id="gpsAcc">-</strong></div>
                    <div><span style="color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Jarak</span><br><strong style="color:var(--text-foreground);" id="gpsDist">-</strong></div>
                </div>
            </div>
        </div>

        @if($todayAttendance)
        <div style="background:var(--bg-card);border:1px solid var(--border-border);border-radius:12px;padding:22px;box-shadow:var(--shadow-subtle);">
            <h3 style="margin:0 0 14px;font-size:14px;font-weight:600;color:var(--text-foreground);letter-spacing:-0.2px;">Status Hari Ini</h3>
            <div style="display:grid;gap:10px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-border);"><span style="color:var(--text-secondary);">Jam Masuk</span><strong style="color:var(--text-foreground);">{{ $todayAttendance->jam_masuk ?? '-' }}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-border);"><span style="color:var(--text-secondary);">Jam Pulang</span><strong style="color:var(--text-foreground);">{{ $todayAttendance->jam_pulang ?? '-' }}</strong></div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;"><span style="color:var(--text-secondary);">Status</span>
                    @if($todayAttendance->status=='tepat_waktu')<span class="badge-success" style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;">Tepat Waktu</span>
                    @elseif($todayAttendance->status=='terlambat')<span class="badge-warning" style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;">Terlambat</span>
                    @elseif($todayAttendance->status=='izin')<span class="badge-info" style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;">Izin</span>
                    @elseif($todayAttendance->status=='sakit')<span class="badge-info" style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;">Sakit</span>
                    @elseif($todayAttendance->status=='libur_nasional')<span style="background:var(--bg-input);color:var(--text-secondary);border:1px solid var(--border-border);padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;">Libur Nasional</span>
                    @else<span class="badge-danger" style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:500;">Tidak Hadir</span>@endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- History --}}
<div class="responsive-table" style="background:var(--bg-card);border:1px solid var(--border-border);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-subtle);">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border-border);">
        <h3 style="margin:0;font-size:14px;font-weight:600;color:var(--text-foreground);letter-spacing:-0.2px;">Riwayat Absensi</h3>
    </div>
    <div>
        <table class="mono-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Tanggal</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Masuk</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Pulang</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--text-muted);font-size:10px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border-border);background:var(--bg-input);">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $att)
                <tr>
                    <td data-label="Tanggal" style="padding:11px 16px;border-bottom:1px solid var(--border-border);font-size:13px;color:var(--text-foreground);">{{ $att->tanggal->format('d M Y') }}</td>
                    <td data-label="Masuk" style="padding:11px 16px;border-bottom:1px solid var(--border-border);font-size:13px;color:var(--text-secondary);font-variant-numeric:tabular-nums;">{{ $att->jam_masuk ?? '-' }}</td>
                    <td data-label="Pulang" style="padding:11px 16px;border-bottom:1px solid var(--border-border);font-size:13px;color:var(--text-secondary);font-variant-numeric:tabular-nums;">{{ $att->jam_pulang ?? '-' }}</td>
                    <td data-label="Status" style="padding:11px 16px;border-bottom:1px solid var(--border-border);">
                        @if($att->status=='tepat_waktu')<span class="badge-success" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Tepat Waktu</span>
                        @elseif($att->status=='terlambat')<span class="badge-warning" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Terlambat</span>
                        @elseif($att->status=='izin')<span class="badge-info" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Izin</span>
                        @elseif($att->status=='sakit')<span class="badge-info" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Sakit</span>
                        @elseif($att->status=='libur_nasional')<span style="background:var(--bg-input);color:var(--text-secondary);border:1px solid var(--border-border);padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Libur Nasional</span>
                        @else<span class="badge-danger" style="padding:3px 10px;border-radius:6px;font-size:10px;font-weight:500;">Tidak Hadir</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">Belum ada riwayat absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const OFFICE_LAT = -6.2355, OFFICE_LNG = 106.8260, MAX_RADIUS = 100, MAX_ACCURACY = 100;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const userFaceData = @json($userFaceData);
    let currentPos = null, videoStream = null, modelsLoaded = false;

    // GPS
    function getGPS() {
        if (!navigator.geolocation) { document.getElementById('gpsStatus').innerHTML = '<p style="color:#777;">GPS tidak tersedia.</p>'; return; }
        navigator.geolocation.watchPosition(pos => {
            currentPos = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
            const dist = haversine(currentPos.lat, currentPos.lng, OFFICE_LAT, OFFICE_LNG);
            document.getElementById('gpsDetails').style.display = 'block';
            document.getElementById('gpsLat').textContent = currentPos.lat.toFixed(6);
            document.getElementById('gpsLng').textContent = currentPos.lng.toFixed(6);
            document.getElementById('gpsAcc').textContent = currentPos.acc.toFixed(0) + ' m';
            document.getElementById('gpsDist').textContent = dist.toFixed(0) + ' m';

            const ok = dist <= MAX_RADIUS && currentPos.acc <= MAX_ACCURACY;
            document.getElementById('gpsStatus').innerHTML = ok
                ? '<p style="color:var(--accent-success); font-weight: 600;">✓ Lokasi valid — dalam radius kantor</p>'
                : '<p style="color:var(--accent-danger); font-weight: 600;">✗ ' + (dist > MAX_RADIUS ? 'Di luar radius (' + dist.toFixed(0) + 'm)' : 'Akurasi rendah (' + currentPos.acc.toFixed(0) + 'm)') + '</p>';
        }, err => {
            document.getElementById('gpsStatus').innerHTML = '<p style="color:var(--accent-danger);">Gagal GPS: ' + err.message + '</p>';
        }, { enableHighAccuracy: true, maximumAge: 10000 });
    }
    getGPS();

    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371000, dLat = (lat2-lat1)*Math.PI/180, dLon = (lon2-lon1)*Math.PI/180;
        const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    // Camera
    async function startAttCamera() {
        try {
            const video = document.getElementById('attVideo');
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 380, height: 285 } });
            videoStream = stream; video.srcObject = stream; video.style.display = 'block';
            document.getElementById('attOverlay').style.display = 'block';
            document.getElementById('camPlaceholder').style.display = 'none';
            document.getElementById('btnStartAtt').style.display = 'none';

            const hasClockIn = {{ $todayAttendance && $todayAttendance->jam_masuk ? 'true' : 'false' }};
            const hasClockOut = {{ $todayAttendance && $todayAttendance->jam_pulang ? 'true' : 'false' }};
            if (!hasClockIn) document.getElementById('btnClockIn').style.display = 'inline-block';
            if (hasClockIn && !hasClockOut) document.getElementById('btnClockOut').style.display = 'inline-block';

            showMsg('Memuat model face recognition...', 'info');
            await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            modelsLoaded = true;
            showMsg('✓ Model siap. Silakan clock in/out.', 'success');
        } catch(e) { showMsg('Gagal kamera: ' + e.message, 'error'); }
    }

    function showMsg(msg, status = 'default') {
        const el = document.getElementById('attMsg');
        el.textContent = msg;
        if (status === 'success') el.style.color = 'var(--accent-success)';
        else if (status === 'error') el.style.color = 'var(--accent-danger)';
        else if (status === 'info') el.style.color = 'var(--accent-info)';
        else el.style.color = 'var(--text-muted)';
    }

    async function captureAndVerify() {
        if (!modelsLoaded) { showMsg('Model belum siap, tunggu sebentar...', 'info'); return null; }
        const video = document.getElementById('attVideo');
        showMsg('Mendeteksi & memverifikasi wajah...', 'info');

        const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks().withFaceDescriptor();

        if (!detection) { showMsg('Wajah tidak terdeteksi!', 'error'); return null; }

        // Compare with stored face
        let faceMatch = false;
        if (userFaceData) {
            const stored = new Float32Array(JSON.parse(userFaceData));
            const dist = faceapi.euclideanDistance(detection.descriptor, stored);
            faceMatch = dist < 0.6; // threshold
            if (!faceMatch) { showMsg('Wajah tidak cocok (jarak: ' + dist.toFixed(3) + '). Coba lagi.', 'error'); return null; }
        }

        // Capture photo
        const canvas = document.getElementById('captureCanvas');
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const foto = canvas.toDataURL('image/png');

        return { foto, faceMatch: true };
    }

    async function doClockIn() {
        if (!currentPos) { showMsg('GPS belum tersedia!', 'error'); return; }
        const result = await captureAndVerify();
        if (!result) return;

        showMsg('Mengirim data clock in...', 'info');
        try {
            const res = await fetch('/attendance/clock-in', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ latitude: currentPos.lat, longitude: currentPos.lng, accuracy: currentPos.acc, foto: result.foto, face_match: result.faceMatch })
            });
            const data = await res.json();
            if (data.success) { showMsg('✓ ' + data.message, 'success'); setTimeout(() => location.reload(), 1500); }
            else { showMsg(data.error || 'Gagal clock in.', 'error'); }
        } catch(e) { showMsg('Error: ' + e.message, 'error'); }
    }

    async function doClockOut() {
        showMsg('Mengirim data clock out...', 'info');
        try {
            const res = await fetch('/attendance/clock-out', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (data.success) { showMsg('✓ ' + data.message, 'success'); setTimeout(() => location.reload(), 1500); }
            else { showMsg(data.error || 'Gagal clock out.', 'error'); }
        } catch(e) { showMsg('Error: ' + e.message, 'error'); }
    }
</script>
@endpush
