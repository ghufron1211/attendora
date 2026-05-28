<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            
            // Anti-flicker: Set sidebar collapse state on desktop before render
            if (window.innerWidth > 768) {
                const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (collapsed) {
                    document.write('<style>#sidebar { width: 72px !important; } #mainContent { margin-left: 72px !important; }</style>');
                }
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Attendora — Smart Internship Attendance & Monitoring Platform">
    <title>@yield('title', 'Dashboard') — Attendora</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/attendora-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .sidebar-logo-box:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.15) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
        }
        @keyframes status-pulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.25); }
        }
    </style>
</head>
<body style="font-family:'Inter',sans-serif; background:var(--bg-background); color:var(--text-foreground); min-height:100vh; margin:0;">
    <div id="sidebarOverlay" onclick="toggleSidebar()" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:30;display:none;"></div>

    <aside id="sidebar" style="background:var(--bg-sidebar);border-right:1px solid var(--border-border);width:260px;min-height:100vh;position:fixed;top:0;left:0;z-index:40;display:flex;flex-direction:column;">
        {{-- Logo & Brand --}}
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo-wrapper">
                <img src="{{ asset('storage/images/attendora-logo.png') }}" alt="Attendora" class="sidebar-logo">
            </div>
            <div style="text-align:center;">
                <h1 style="font-size:18px;font-weight:800;margin:0;letter-spacing:-0.5px;color:var(--text-foreground);background:linear-gradient(135deg,var(--text-foreground) 0%,var(--text-secondary) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Attendora</h1>
                <p style="font-size:10.5px;color:var(--text-secondary);opacity:0.6;margin:2px 0 0;letter-spacing:0.5px;font-weight:500;">Smart Attendance System</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav style="padding:16px 0;flex:1;overflow-y:auto;">
            <p style="padding:8px 20px;font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-muted);font-weight:600;margin:0;">Menu</p>

            {{-- Dashboard: semua role --}}
            <a href="/dashboard" style="display:flex;align-items:center;gap:10px;padding:10px 16px;margin:2px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;transition:all .2s;{{ request()->is('dashboard*') ? 'background:var(--accent-active);color:var(--text-foreground);' : 'color:var(--text-secondary);' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <span>Dashboard</span>
            </a>

            {{-- Absensi & Logbook: hanya user --}}
            @if(!auth()->user()->isAdmin())
            <a href="/attendance" style="display:flex;align-items:center;gap:10px;padding:10px 16px;margin:2px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;transition:all .2s;{{ request()->is('attendance*') ? 'background:var(--accent-active);color:var(--text-foreground);' : 'color:var(--text-secondary);' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span>Absensi</span>
            </a>
            <a href="/logbook" style="display:flex;align-items:center;gap:10px;padding:10px 16px;margin:2px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;transition:all .2s;{{ request()->is('logbook*') ? 'background:var(--accent-active);color:var(--text-foreground);' : 'color:var(--text-secondary);' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                <span>Logbook</span>
            </a>
            @endif

            {{-- Peta Absensi: semua role --}}
            <a href="/map" style="display:flex;align-items:center;gap:10px;padding:10px 16px;margin:2px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;transition:all .2s;{{ request()->is('map*') ? 'background:var(--accent-active);color:var(--text-foreground);' : 'color:var(--text-secondary);' }}">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Peta Absensi</span>
            </a>

            {{-- Admin section: hanya admin --}}
            @if(auth()->user()->isAdmin())
            <p style="padding:18px 20px 8px;font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-muted);font-weight:600;margin:0;">Admin</p>
            @php $adminLinks = [
                ['url'=>'/admin/users','label'=>'Kelola User','icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','match'=>'admin/users'],
                ['url'=>'/admin/logbook','label'=>'Review Logbook','icon'=>'<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><path d="M9 14l2 2 4-4"/>','match'=>'admin/logbook'],
                ['url'=>'/admin/holidays','label'=>'Kelola Hari Libur','icon'=>'<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>','match'=>'admin/holidays'],
                ['url'=>'/admin/export/excel','label'=>'Export Excel','icon'=>'<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>','match'=>'admin/export'],
            ]; @endphp
            @foreach($adminLinks as $l)
                <a href="{{ $l['url'] }}" style="display:flex;align-items:center;gap:10px;padding:10px 16px;margin:2px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;transition:all .2s;{{ request()->is($l['match'].'*') ? 'background:var(--accent-active);color:var(--text-foreground);' : 'color:var(--text-secondary);' }}">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $l['icon'] !!}</svg>
                    <span>{{ $l['label'] }}</span>
                </a>
            @endforeach
            @endif
        </nav>

        {{-- User profile --}}
        <div style="padding:16px;border-top:1px solid var(--border-border);">
            <div class="user-profile-wrapper" style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <div style="width:34px;height:34px;background:var(--bg-card);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;color:var(--text-secondary);border:1px solid var(--border-border);flex-shrink:0;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div class="user-profile-details" style="flex:1;min-width:0;">
                    <p style="font-weight:500;font-size:13px;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-foreground);">{{ auth()->user()->name }}</p>
                    <span style="font-size:10px;padding:2px 8px;border-radius:6px;background:var(--accent-white);color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
            <form method="POST" action="/logout" class="logout-form">@csrf
                <button type="submit" style="width:100%;padding:8px;background:transparent;border:1px solid var(--border-border);border-radius:8px;color:var(--text-secondary);cursor:pointer;font-weight:500;font-size:12px;transition:all .2s;" onmouseover="this.style.background='var(--text-foreground)';this.style.color='var(--bg-background)';this.style.borderColor='var(--text-foreground)'" onmouseout="this.style.background='transparent';this.style.color='var(--text-secondary)';this.style.borderColor='var(--border-border)'">Logout</button>
            </form>
        </div>
    </aside>

    <main id="mainContent" style="margin-left:260px;">
        <header class="px-4 py-3 md:px-8 md:py-4" style="border-bottom:1px solid var(--border-border);display:flex;align-items:center;justify-content:space-between;background:var(--bg-header);backdrop-filter:blur(12px);position:sticky;top:0;z-index:20;">
            <div style="display:flex;align-items:center;gap:14px;">
                {{-- Mobile Hamburger --}}
                <button onclick="toggleSidebar()" id="menuBtn" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;display:none;align-items:center;justify-content:center;padding:4px;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                {{-- Desktop Collapse Toggle --}}
                <button onclick="toggleCollapse()" id="collapseBtn" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;display:none;align-items:center;justify-content:center;padding:4px;outline:none;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                </button>
                <img src="{{ asset('storage/images/attendora-logo.png') }}" alt="Attendora" class="topbar-logo" style="width:36px;height:36px;">
                <div>
                    <h2 class="text-responsive-base" style="font-weight:600;margin:0;letter-spacing:-0.3px;color:var(--text-foreground);">@yield('title','Dashboard')</h2>
                    <p style="font-size:11px;color:var(--text-muted);margin:0;" class="user-profile-details">@yield('subtitle','')</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="display:flex;align-items:center;gap:6px;background:var(--accent-white);border:1px solid var(--border-border);padding:4px 10px;border-radius:100px;font-size:10.5px;color:var(--text-secondary);font-weight:500;letter-spacing:0.3px;" class="user-profile-details">
                    <span style="display:inline-block;width:5px;height:5px;background:#10b981;border-radius:50%;box-shadow:0 0 8px #10b981;animation:status-pulse 2s infinite ease-in-out;"></span>
                    SaaS System
                </div>
                
                {{-- Theme Switcher Button --}}
                <button id="themeToggleBtn" onclick="toggleTheme()" style="background:none;border:1px solid var(--border-border);color:var(--text-secondary);cursor:pointer;padding:8px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:all .2s;outline:none;" onmouseover="this.style.background='var(--accent-white)';this.style.color='var(--text-foreground)'" onmouseout="this.style.background='none';this.style.color='var(--text-secondary)'">
                    <svg id="sunIcon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg id="moonIcon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="display:none;"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>

                <span style="font-size:11px;color:var(--text-muted);font-variant-numeric:tabular-nums;" id="currentTime" class="user-profile-details"></span>
            </div>
        </header>
        <div class="px-4 pt-4 md:px-8 md:pt-6 pb-0">
            @if(session('success'))<div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;background:var(--accent-success-glow);border:1px solid rgba(16, 185, 129, 0.15);color:var(--accent-success);display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;">✓ {{ session('success') }}</div>@endif
            @if(session('error'))<div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;background:var(--accent-danger-glow);border:1px solid rgba(239, 68, 68, 0.15);color:var(--accent-danger);display:flex;align-items:center;gap:10px;font-size:13px;font-weight:500;">✗ {{ session('error') }}</div>@endif
            @if($errors->any())<div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;background:var(--accent-danger-glow);border:1px solid rgba(239, 68, 68, 0.15);color:var(--accent-danger);font-size:13px;font-weight:500;padding-bottom:2px;">@foreach($errors->all() as $e)<p style="margin:2px 0;">• {{ $e }}</p>@endforeach</div>@endif
        </div>
        <div class="p-4 md:p-8">@yield('content')</div>

        {{-- Footer --}}
        <footer class="p-4 md:p-8" style="border-top:1px solid var(--border-primary);text-align:center;">
            <p style="margin:0;font-size:11px;color:var(--text-muted);letter-spacing:0.3px;">© {{ date('Y') }} Attendora — Smart Attendance & Internship Monitoring Platform</p>
        </footer>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        function updateClock(){const n=new Date();const el=document.getElementById('currentTime');if(el)el.textContent=n.toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false});}
        updateClock();setInterval(updateClock,1000);
        function toggleSidebar(){const s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay');s.classList.toggle('open');o.style.display=s.classList.contains('open')?'block':'none';}
        
        function toggleCollapse() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const collapseBtn = document.getElementById('collapseBtn');
            const isCollapsed = sidebar.classList.toggle('collapsed');
            
            if (isCollapsed) {
                mainContent.style.marginLeft = '72px';
                mainContent.classList.add('expanded-margin');
                localStorage.setItem('sidebarCollapsed', 'true');
                collapseBtn.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
            } else {
                mainContent.style.marginLeft = '260px';
                mainContent.classList.remove('expanded-margin');
                localStorage.setItem('sidebarCollapsed', 'false');
                collapseBtn.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>';
            }
            window.dispatchEvent(new Event('resize')); // Recalculate charts/maps
        }

        function checkResp(){
            const m=document.getElementById('menuBtn');
            const c=document.getElementById('collapseBtn');
            const sidebar=document.getElementById('sidebar');
            const mainContent=document.getElementById('mainContent');
            
            if(window.innerWidth<=768){
                if(m) m.style.display='block';
                if(c) c.style.display='none';
                mainContent.style.marginLeft='0';
                mainContent.classList.remove('expanded-margin');
                sidebar.classList.remove('collapsed');
            }else{
                if(m) m.style.display='none';
                if(c) c.style.display='flex';
                sidebar.classList.remove('open');
                const overlay=document.getElementById('sidebarOverlay');
                if(overlay) overlay.style.display='none';
                
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    mainContent.style.marginLeft = '72px';
                    mainContent.classList.add('expanded-margin');
                    c.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.style.marginLeft = '260px';
                    mainContent.classList.remove('expanded-margin');
                    c.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>';
                }
            }
        }
        checkResp();window.addEventListener('resize',checkResp);

        // Theme Switcher Logic
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcons(newTheme);
            if (typeof window.updateChartsForTheme === 'function') {
                window.updateChartsForTheme(newTheme);
            }
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
    @stack('scripts')
</body>
</html>

