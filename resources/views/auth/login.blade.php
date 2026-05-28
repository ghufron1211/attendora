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
    <meta name="description" content="Attendora — Smart Internship Attendance Platform. Login to your account.">
    <title>Login — Attendora</title>
    <link rel="icon" type="image/png" href="{{ asset('images/attendora-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body, .login-card, .login-input, .login-btn {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease, opacity 0.3s ease;
        }
        .login-card {
            background: var(--bg-card-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 32px 40px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.03) inset;
            position: relative;
            overflow: hidden;
        }
        [data-theme="light"] .login-card {
            box-shadow: 0 32px 80px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.03) inset;
        }
        .login-card::before {
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
        .login-input {
            width: 100%;
            padding: 12px 14px;
            background: var(--bg-input);
            border: 1px solid var(--border-border);
            border-radius: 10px;
            color: var(--text-foreground);
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: all .2s ease;
            font-family: 'Inter', sans-serif;
        }
        .login-input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px var(--accent-primary-glow);
        }
        .login-btn {
            width: 100%;
            padding: 13px;
            background: var(--text-foreground);
            color: var(--bg-background);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all .25s cubic-bezier(0.16,1,0.3,1);
            margin-bottom: 18px;
            letter-spacing: -0.2px;
            font-family: 'Inter', sans-serif;
        }
        .login-btn:hover {
            background: var(--text-secondary);
            color: var(--bg-background);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(255,255,255,0.08);
        }
        [data-theme="light"] .login-btn:hover {
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
            .login-card {
                padding: 24px 20px 28px !important;
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
<body style="font-family:'Inter',sans-serif;background:var(--bg-background);color:var(--text-foreground);min-height:100vh;margin:0;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden;">
    {{-- Subtle ambient glow orbs --}}
    <div class="glow-orb" style="width:400px;height:400px;background:var(--accent-primary);top:-100px;right:-100px;"></div>
    <div class="glow-orb" style="width:300px;height:300px;background:var(--text-muted);bottom:-80px;left:-80px;"></div>

    {{-- Theme Switcher Button --}}
    <button id="themeToggleBtn" onclick="toggleTheme()" style="position:absolute;top:24px;right:28px;background:var(--bg-card-glass);border:1px solid var(--border-glass);color:var(--text-secondary);cursor:pointer;padding:8px;border-radius:10px;display:flex;align-items:center;justify-content:center;transition:all .3s;z-index:100;outline:none;" onmouseover="this.style.background='var(--accent-white)';this.style.color='var(--text-foreground)'" onmouseout="this.style.background='var(--bg-card-glass)';this.style.color='var(--text-secondary)'">
        <svg id="sunIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg id="moonIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:none;"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
    </button>

    <div class="login-card" style="z-index:1;">
        <div class="auth-brand">
            <img src="{{ asset('images/attendora-logo.png') }}" class="auth-logo" alt="Attendora Logo">
            <h1 class="auth-title">Attendora</h1>
            <p class="auth-subtitle">Smart Internship Attendance Platform</p>
        </div>

        @if(session('success'))
        <div style="padding:12px 16px;border-radius:10px;margin-bottom:18px;background:var(--accent-success-glow);border:1px solid rgba(16, 185, 129, 0.15);color:var(--accent-success);font-size:13px;display:flex;align-items:center;gap:8px;font-weight:500;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div style="padding:12px 16px;border-radius:10px;margin-bottom:18px;background:var(--accent-danger-glow);border:1px solid rgba(239, 68, 68, 0.15);color:var(--accent-danger);font-size:13px;font-weight:500;">
            @foreach($errors->all() as $e)<p style="margin:2px 0;">• {{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="/login">
            @csrf
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:6px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.8px;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="login-input" placeholder="email@example.com">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:6px;font-weight:500;color:var(--text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.8px;">Password</label>
                <input type="password" name="password" required class="login-input" placeholder="Masukkan password">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
                <label style="display:flex;align-items:center;gap:8px;color:var(--text-secondary);font-size:12px;cursor:pointer;">
                    <input type="checkbox" name="remember" style="accent-color:var(--text-foreground);"> Ingat saya
                </label>
            </div>
            <button type="submit" class="login-btn">Masuk</button>
        </form>

        <p style="text-align:center;font-size:13px;color:var(--text-muted);margin:0;">Belum punya akun? <a href="/register" style="color:var(--text-secondary);text-decoration:none;font-weight:600;transition:color .2s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">Daftar sekarang</a></p>
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
</body>
</html>
