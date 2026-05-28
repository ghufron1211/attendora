@extends('layouts.app')
@section('title', 'Peta Absensi')
@section('subtitle', 'Lokasi absensi di peta')

@push('styles')
<style>
    #mapContainer { height: calc(100vh - 200px); min-height: 400px; border-radius: 12px; overflow: hidden; border: 1px solid #1a1a1a; }
</style>
@endpush

@section('content')
<div id="mapContainer"></div>
@endsection

@push('scripts')
<script>
    const markers = @json($markers);
    const officeLat = -6.2355, officeLng = 106.8260;

    const map = L.map('mapContainer').setView([officeLat, officeLng], 15);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        maxZoom: 19
    }).addTo(map);

    // Office marker — monochrome white
    const officeIcon = L.divIcon({
        html: '<div style="width:30px;height:30px;background:#fff;border-radius:50%;border:2px solid #333;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 12px rgba(0,0,0,0.5);"><svg width="14" height="14" fill="#0a0a0a" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        className: ''
    });
    L.marker([officeLat, officeLng], { icon: officeIcon }).addTo(map)
        .bindPopup('<div style="font-family:Inter,sans-serif;font-size:13px;color:#f5f5f5;"><strong>📍 Lokasi Kantor</strong><br>Lat: ' + officeLat + '<br>Lng: ' + officeLng + '<br>Radius: 100m</div>');

    // 100m radius circle — monochrome
    L.circle([officeLat, officeLng], { radius: 100, color: '#555', fillColor: '#fff', fillOpacity: 0.04, weight: 1 }).addTo(map);

    // Attendance markers — monochrome
    markers.forEach(m => {
        let color = '#fff';        // tepat_waktu = white
        let size = 12;
        if (m.status === 'terlambat') { color = '#888'; }  // gray
        if (m.status === 'tidak_hadir') { color = '#444'; } // dark gray

        const icon = L.divIcon({
            html: '<div style="width:' + size + 'px;height:' + size + 'px;background:' + color + ';border-radius:50%;border:2px solid #222;box-shadow:0 1px 6px rgba(0,0,0,0.4);"></div>',
            iconSize: [size, size],
            iconAnchor: [size/2, size/2],
            className: ''
        });

        const statusLabel = m.status === 'tepat_waktu' ? 'Tepat Waktu' : m.status === 'terlambat' ? 'Terlambat' : 'Tidak Hadir';

        L.marker([m.lat, m.lng], { icon }).addTo(map)
            .bindPopup('<div style="font-family:Inter,sans-serif;font-size:12px;color:#f5f5f5;"><strong>' + m.name + '</strong><br>📅 ' + m.tanggal + '<br>⏰ ' + m.jam_masuk + '<br>Status: <span style="color:' + color + ';font-weight:600;">' + statusLabel + '</span></div>');
    });
</script>
@endpush
