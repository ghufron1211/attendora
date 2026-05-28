<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Attendora — Create your account for smart internship attendance tracking.">
    <title>Register — Attendora</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/attendora-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body, .register-card, .reg-input, .reg-btn {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease, opacity 0.3s ease;
        }
        .register-card {
            background: var(--bg-card-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 24px 36px 36px;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.03) inset;
            position: relative;
            overflow: hidden;
        }
        [data-theme="light"] .register-card {
            box-shadow: 0 32px 80px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.03) inset;
        }
        .register-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 1px;
            background: linear-gradient(180deg, var(--border-glass), transparent 50%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        .reg-input {
            width: 100%;
            padding: 10px 12px;
            background: var(--bg-input);
            border: 1px solid var(--border-border);
            border-radius: 8px;
            color: var(--text-foreground);
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
            transition: all .2s;
            font-family: 'Inter', sans-serif;
        }
        .reg-input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px var(--accent-primary-glow);
        }
        .reg-btn {
            width: 100%;
            padding: 12px;
            background: var(--text-foreground);
            color: var(--bg-background);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all .25s cubic-bezier(0.16,1,0.3,1);
            margin-bottom: 14px;
            letter-spacing: -0.2px;
            font-family: 'Inter', sans-serif;
        }
        .reg-btn:hover {
            background: var(--text-secondary);
            color: var(--bg-background);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(255,255,255,0.08);
        }
        [data-theme="light"] .reg-btn:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        .glow-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: var(--glow-orb-opacity);
            pointer-events: none;
            z-index: 0;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 12px;
        }
        .auth-logo {
            display: inline-block;
            animation: fadeInUp .7s cubic-bezier(0.16,1,0.3,1) both;
        }
        .brand-title {
            font-size:32px;
            font-weight:800;
            margin:0;
            letter-spacing:-0.8px;
            background:linear-gradient(135deg,var(--text-foreground) 0%,var(--text-secondary) 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            animation:fadeInUp .7s ease .15s both;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 480px) {
            .register-card {
                padding: 24px 16px 28px !important;
                border-radius: 16px !important;
            }
            .auth-logo {
                width: 72px !important;
                height: 72px !important;
            }
            .auth-title {
                font-size: 1.65rem !important;
            }
            .auth-subtitle {
                font-size: 0.85rem !important;
            }
        }
    </style>
</head>
<body style="font-family:'Inter',sans-serif;background:var(--bg-background);color:var(--text-foreground);min-height:100vh;margin:0;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow-x:hidden;">
    {{-- Ambient glow --}}
    <div class="glow-orb" style="width:350px;height:350px;background:var(--accent-primary);top:-80px;left:-80px;"></div>
    <div class="glow-orb" style="width:280px;height:280px;background:var(--text-muted);bottom:-60px;right:-60px;"></div>

    {{-- Theme Switcher Button --}}
    <button id="themeToggleBtn" onclick="toggleTheme()" style="position:absolute;top:24px;right:28px;background:var(--bg-card-glass);border:1px solid var(--border-glass);color:var(--text-secondary);cursor:pointer;padding:8px;border-radius:10px;display:flex;align-items:center;justify-content:center;transition:all .3s;z-index:100;outline:none;" onmouseover="this.style.background='var(--accent-white)';this.style.color='var(--text-foreground)'" onmouseout="this.style.background='var(--bg-card-glass)';this.style.color='var(--text-secondary)'">
        <svg id="sunIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg id="moonIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:none;"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
    </button>

    <div class="register-card" style="z-index:1;">
        <div class="auth-brand">
            <img src="{{ asset('storage/images/attendora-logo.png') }}" class="auth-logo" alt="Attendora Logo">
            <h1 class="auth-title">Attendora</h1>
            <p class="auth-subtitle">Smart Internship Attendance Platform</p>
        </div>

        @if($errors->any())
        <div style="padding:12px 16px;border-radius:10px;margin-bottom:16px;background:var(--accent-danger-glow);border:1px solid rgba(239, 68, 68, 0.15);color:var(--accent-danger);font-size:13px;font-weight:500;">
            @foreach($errors->all() as $e)<p style="margin:2px 0;">• {{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="/register" id="registerForm">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3.5">
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required class="reg-input" placeholder="johndoe">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="reg-input" placeholder="John Doe">
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="reg-input" placeholder="email@example.com">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3.5">
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Nomor Telepon</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp') }}" required class="reg-input" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Asal Instansi</label>
                    <input type="text" name="asal_instansi" value="{{ old('asal_instansi') }}" required class="reg-input" placeholder="Universitas/Sekolah">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3.5">
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Password</label>
                    <input type="password" name="password" required class="reg-input" placeholder="Min. 8 karakter">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="reg-input" placeholder="Ulangi password">
                </div>
            </div>

            {{-- Face capture --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Registrasi Wajah <span style="color:var(--accent-danger);">*</span></label>
                <div style="background:var(--bg-input);border:1px solid var(--border-border);border-radius:10px;padding:14px;text-align:center;">
                    <video id="regVideo" autoplay muted playsinline style="width:100%;max-width:300px;border-radius:8px;display:none;"></video>
                    <canvas id="regCanvas" style="display:none;"></canvas>
                    <div id="faceStatus" style="padding:16px;color:var(--text-secondary);font-size:12px;">
                        <p>Klik tombol di bawah untuk memulai kamera</p>
                    </div>
                    <div style="display:flex;gap:8px;justify-content:center;margin-top:10px;">
                        <button type="button" onclick="startCamera()" id="btnStartCam" style="padding:8px 16px;background:var(--text-foreground);color:var(--bg-background);border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;transition:all .2s;" onmouseover="this.style.background='var(--text-secondary)'" onmouseout="this.style.background='var(--text-foreground)'">Buka Kamera</button>
                        <button type="button" onclick="captureFace()" id="btnCapture" style="padding:8px 16px;background:var(--bg-card);color:var(--text-foreground);border:1px solid var(--border-border);border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;display:none;transition:all .2s;" onmouseover="this.style.background='var(--bg-card-hover)'" onmouseout="this.style.background='var(--bg-card)'">Capture Wajah</button>
                    </div>
                    <p id="faceMsg" style="font-size:11px;color:var(--text-muted);margin:8px 0 0;"></p>
                </div>
                <input type="hidden" name="face_data" id="faceDataInput">
            </div>

            <button type="submit" class="reg-btn">Daftar</button>
        </form>

        <p style="text-align:center;font-size:13px;color:var(--text-muted);margin:0;">Sudah punya akun? <a href="/login" style="color:var(--text-secondary);text-decoration:none;font-weight:600;transition:color .2s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">Login di sini</a></p>
    </div>

    <script>
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcons(newTheme);
        }
        function updateToggleIcons(theme) {
            const sunIcon = document.getElementById('sunIcon');
            const moonIcon = document.getElementById('moonIcon');
            if (sunIcon && moonIcon) {
                if (theme === 'light') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            updateToggleIcons(activeTheme);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        let videoStream = null;
        let faceDescriptor = null;

        async function startCamera() {
            try {
                const video = document.getElementById('regVideo');
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 320, height: 240 } });
                videoStream = stream;
                video.srcObject = stream;
                video.style.display = 'block';
                document.getElementById('faceStatus').style.display = 'none';
                document.getElementById('btnStartCam').style.display = 'none';
                document.getElementById('btnCapture').style.display = 'inline-block';
                document.getElementById('faceMsg').textContent = 'Memuat model face recognition...';
                document.getElementById('faceMsg').style.color = '#888';

                await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
                document.getElementById('faceMsg').textContent = 'Model siap. Arahkan wajah ke kamera lalu klik "Capture Wajah".';
                document.getElementById('faceMsg').style.color = '#999';
            } catch (e) {
                document.getElementById('faceMsg').textContent = 'Gagal mengakses kamera: ' + e.message;
                document.getElementById('faceMsg').style.color = '#777';
            }
        }

        async function captureFace() {
            const video = document.getElementById('regVideo');
            const msg = document.getElementById('faceMsg');
            msg.textContent = 'Mendeteksi wajah...';
            msg.style.color = '#888';

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                msg.textContent = 'Wajah tidak terdeteksi. Pastikan wajah terlihat jelas.';
                msg.style.color = '#777';
                return;
            }

            faceDescriptor = Array.from(detection.descriptor);
            document.getElementById('faceDataInput').value = JSON.stringify(faceDescriptor);

            msg.textContent = '✓ Wajah berhasil direkam!';
            msg.style.color = '#ccc';
            document.getElementById('btnCapture').textContent = 'Capture Ulang';
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!document.getElementById('faceDataInput').value) {
                e.preventDefault();
                document.getElementById('faceMsg').textContent = 'Silakan capture wajah terlebih dahulu!';
                document.getElementById('faceMsg').style.color = '#777';
            }
        });
    </script>
</body>
</html>
