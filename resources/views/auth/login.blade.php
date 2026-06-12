<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | Golden Bird CRM Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #000d1f;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ── LEFT PANEL ─────────────────────────────────── */
        .left-panel {
            flex: 1;
            position: relative;
            display: none; /* hidden on mobile */
            overflow: hidden;
        }
        @media (min-width: 900px) { .left-panel { display: flex; flex-direction: column; } }

        /* Armada background — deep blue fleet atmosphere */
        .left-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 30% 40%, rgba(0,82,204,0.35) 0%, transparent 65%),
                radial-gradient(ellipse 60% 80% at 70% 70%, rgba(0,30,100,0.6) 0%, transparent 60%),
                linear-gradient(160deg, #000d1f 0%, #001a3a 40%, #002052 70%, #000d1f 100%);
        }

        /* Grid overlay */
        .left-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,102,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,102,255,0.06) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Perspective road/fleet lines */
        .fleet-lines {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 45%;
            background:
                linear-gradient(to bottom,
                    transparent 0%,
                    rgba(0,60,180,0.08) 40%,
                    rgba(0,60,180,0.18) 100%
                );
            clip-path: polygon(0% 100%, 100% 100%, 80% 0%, 20% 0%);
        }

        /* Floating bus silhouettes */
        .bus-fleet {
            position: absolute;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }
        .bus {
            background: rgba(0,60,180,0.3);
            border: 1px solid rgba(0,102,255,0.25);
            border-radius: 8px 8px 4px 4px;
            position: relative;
            box-shadow: 0 0 30px rgba(0,82,204,0.15);
        }
        .bus::after {
            content: '';
            position: absolute;
            bottom: -6px; left: 10%; right: 10%;
            height: 6px;
            background: rgba(0,102,255,0.3);
            border-radius: 0 0 4px 4px;
        }
        .bus-lg { width: 110px; height: 50px; }
        .bus-md { width: 90px; height: 44px; }
        .bus-sm { width: 70px; height: 36px; opacity: 0.6; }

        /* Window strips on buses */
        .bus-windows {
            position: absolute;
            top: 10px; left: 8px; right: 8px;
            display: flex; gap: 5px;
        }
        .bus-win {
            height: 16px;
            background: rgba(0,150,255,0.35);
            border-radius: 2px;
            flex: 1;
            box-shadow: 0 0 6px rgba(0,150,255,0.2);
        }

        /* Glow orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
        }
        .orb-1 { width: 350px; height: 350px; background: #0052cc; opacity: 0.2; top: -60px; left: -60px; }
        .orb-2 { width: 250px; height: 250px; background: #001f6e; opacity: 0.35; bottom: 100px; right: -40px; }
        .orb-3 { width: 180px; height: 180px; background: #0066ff; opacity: 0.12; top: 40%; left: 30%; }

        /* Left panel content */
        .left-content {
            position: relative;
            z-index: 10;
            padding: 48px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .left-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: auto;
        }
        .left-tagline {
            margin-bottom: 48px;
        }

        /* Stat chips */
        .stat-chip {
            background: rgba(0,30,80,0.6);
            border: 1px solid rgba(0,102,255,0.2);
            border-radius: 12px;
            padding: 14px 18px;
            backdrop-filter: blur(10px);
        }

        /* ── RIGHT PANEL ─────────────────────────────────── */
        .right-panel {
            width: 100%;
            max-width: 480px;
            background: rgba(0, 6, 20, 0.95);
            border-left: 1px solid rgba(0,102,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 36px;
            position: relative;
            overflow-y: auto;
        }
        @media (min-width: 900px) { .right-panel { width: 480px; flex-shrink: 0; } }

        /* Blue top accent line */
        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 20%; right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #0066ff, #3385ff, #0066ff, transparent);
            border-radius: 0 0 2px 2px;
        }

        /* Form card */
        .form-wrap { width: 100%; max-width: 380px; }

        /* Input */
        .inp {
            width: 100%;
            background: rgba(0,20,60,0.4);
            border: 1px solid rgba(0,82,204,0.22);
            border-radius: 10px;
            padding: 12px 14px;
            color: #e2e8f0;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            outline: none;
        }
        .inp:focus {
            border-color: rgba(0,102,255,0.6);
            box-shadow: 0 0 0 3px rgba(0,82,204,0.12);
            background: rgba(0,40,100,0.3);
        }
        .inp::placeholder { color: #1e3a6e; }

        .lbl {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #3d5a99;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        /* Primary blue button */
        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #0052cc 0%, #0066ff 50%, #1a75ff 100%);
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            padding: 14px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 20px rgba(0,82,204,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0066ff, #3385ff, #4d94ff);
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(0,102,255,0.5);
        }
        .btn-primary:active { transform: translateY(0); }

        /* 1-click demo button */
        .btn-demo {
            width: 100%;
            background: rgba(0,50,150,0.2);
            color: #66a3ff;
            font-weight: 700;
            font-size: 13px;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1px solid rgba(0,102,255,0.25);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-demo:hover {
            background: rgba(0,82,204,0.25);
            border-color: rgba(0,102,255,0.45);
            color: #99c2ff;
            box-shadow: 0 0 16px rgba(0,82,204,0.15);
        }

        /* Demo account pills */
        .demo-pill {
            background: rgba(0,25,70,0.5);
            border: 1px solid rgba(0,82,204,0.18);
            border-radius: 8px;
            padding: 8px 10px;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .demo-pill:hover {
            background: rgba(0,82,204,0.18);
            border-color: rgba(0,102,255,0.35);
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .pulse { animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.4;} }

        .divider {
            display: flex; align-items: center; gap: 12px;
            color: #1a3060; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(0,82,204,0.15);
        }

        /* Animate buses gently */
        @keyframes drift { 0%,100%{transform:translateX(-50%) translateY(0);} 50%{transform:translateX(-50%) translateY(-8px);} }
        .bus-fleet { animation: drift 6s ease-in-out infinite; }
    </style>
</head>
<body>

    {{-- ═══ LEFT PANEL — Fleet Armada Visual ═══ --}}
    <div class="left-panel">
        <div class="left-bg"></div>
        <div class="left-grid"></div>
        <div class="fleet-lines"></div>

        {{-- Glow orbs --}}
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        {{-- Bus fleet silhouettes --}}
        <div class="bus-fleet">
            <div class="bus bus-sm">
                <div class="bus-windows">
                    <div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div>
                </div>
            </div>
            <div class="bus bus-lg">
                <div class="bus-windows">
                    <div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div>
                </div>
            </div>
            <div class="bus bus-md">
                <div class="bus-windows">
                    <div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div>
                </div>
            </div>
            <div class="bus bus-sm" style="opacity:0.5;">
                <div class="bus-windows">
                    <div class="bus-win"></div><div class="bus-win"></div><div class="bus-win"></div>
                </div>
            </div>
        </div>

        {{-- Left content --}}
        <div class="left-content">
            {{-- Logo --}}
            <div class="left-logo">
                <img src="/images/golden-bird-logo.svg" alt="Golden Bird" style="width:52px;height:52px;border-radius:14px;background:rgba(0,82,204,0.15);border:1px solid rgba(0,102,255,0.3);padding:6px;">
                <div>
                    <div class="text-lg font-black text-gray-900 tracking-tight">Golden Bird <span style="color:#3385ff;">CRM</span></div>
                    <div class="text-[10px] font-semibold uppercase tracking-widest" style="color:#1e4080;">Command Center</div>
                </div>
            </div>

            {{-- Tagline --}}
            <div class="left-tagline">
                <h2 class="text-3xl font-black text-gray-900 leading-tight mb-3">
                    Kelola Armada<br>
                    <span style="color:#3385ff;">B2B Fleet</span><br>
                    dari Satu Dashboard
                </h2>
                <p class="text-sm leading-relaxed mb-6" style="color:#2d4a7a;">
                    Tracking kendaraan, pipeline sales, invoice & approval — semua terintegrasi real-time.
                </p>

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="stat-chip text-center">
                        <div class="text-xl font-black" style="color:#3385ff;">500+</div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#1e4080;">Armada</div>
                    </div>
                    <div class="stat-chip text-center">
                        <div class="text-xl font-black" style="color:#3385ff;">128</div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#1e4080;">Klien Korporat</div>
                    </div>
                    <div class="stat-chip text-center">
                        <div class="text-xl font-black" style="color:#3385ff;">99%</div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#1e4080;">Uptime</div>
                    </div>
                </div>
            </div>

            {{-- Bottom badges --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span style="background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.2);font-size:9px;font-weight:700;padding:3px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.05em;" class="flex items-center gap-1.5">
                    <span class="pulse" style="width:5px;height:5px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                    Live on Render
                </span>
                <span style="background:rgba(0,82,204,0.15);color:#66a3ff;border:1px solid rgba(0,102,255,0.2);font-size:9px;font-weight:700;padding:3px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.05em;">v7.7</span>
                <span style="background:rgba(0,82,204,0.15);color:#66a3ff;border:1px solid rgba(0,102,255,0.2);font-size:9px;font-weight:700;padding:3px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.05em;">Laravel 12</span>
            </div>
        </div>
    </div>

    {{-- ═══ RIGHT PANEL — Login Form ═══ --}}
    <div class="right-panel">
        <div class="form-wrap">

            {{-- Mobile logo (hidden on desktop) --}}
            <div class="flex items-center gap-3 mb-8" style="display:flex;" @media(min-width:900px){display:none!important;}>
                <img src="/images/golden-bird-logo.svg" alt="Logo"
                     style="width:40px;height:40px;border-radius:10px;background:rgba(0,82,204,0.15);border:1px solid rgba(0,102,255,0.3);padding:5px;">
                <div>
                    <div class="text-base font-black text-gray-900">Golden Bird <span style="color:#3385ff;">CRM</span></div>
                    <div class="text-[9px] uppercase tracking-widest font-semibold" style="color:#1e4080;">Command Center</div>
                </div>
            </div>

            {{-- Header --}}
            <div class="mb-7">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight mb-1">Selamat Datang 👋</h1>
                <p class="text-sm" style="color:#2d4a7a;">Masuk ke Golden Bird CRM Command Center</p>
            </div>

            {{-- Error --}}
            @if($errors->any())
            <div class="mb-4 flex items-center gap-2 px-3 py-3 rounded-lg text-xs font-semibold"
                 style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#f87171;">
                <span class="material-symbols-outlined text-[15px]">error</span>
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Login Form via Dynamic Dropdown --}}
            <div x-data="{
                users: @js($users),
                selectedRole: '',
                selectedEmail: '',
                selectedPassword: '',
                get filteredUsers() {
                    if (!this.selectedRole) return [];
                    return this.users.filter(u => u.role === this.selectedRole);
                },
                updateAccount(email) {
                    this.selectedEmail = email;
                    this.selectedPassword = email.endsWith('@demo.crm') ? 'password' : 'password123';
                }
            }">
                <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    
                    <input type="hidden" name="email" :value="selectedEmail">
                    <input type="hidden" name="password" :value="selectedPassword">

                    <div>
                        <label class="lbl">Kategori Jabatan</label>
                        <select x-model="selectedRole" @change="selectedEmail = ''; selectedPassword = ''" class="inp" style="background-color: rgba(0, 20, 60, 0.95);">
                            <option class="text-slate-900" value="">-- Pilih Kategori Jabatan --</option>
                            <option class="text-slate-900" value="gm">🏢 General Manager (GM)</option>
                            <option class="text-slate-900" value="manager">📊 Sales Manager</option>
                            <option class="text-slate-900" value="sales">💼 Sales Representative</option>
                            <option class="text-slate-900" value="finance">💰 Finance</option>
                            <option class="text-slate-900" value="operational">⚙️ Operational (Ops)</option>
                        </select>
                    </div>

                    <div x-show="selectedRole" x-transition class="space-y-1">
                        <label class="lbl">Pilih Akun / Nama</label>
                        <select @change="updateAccount($event.target.value)" class="inp" style="background-color: rgba(0, 20, 60, 0.95);">
                            <option class="text-slate-900" value="">-- Pilih Nama Akun --</option>
                            <template x-for="u in filteredUsers" :key="u.email">
                                <option class="text-slate-900" :value="u.email" x-text="u.manager_name ? `${u.name} (Tim: ${u.manager_name})` : u.name"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex items-center justify-between" x-show="selectedEmail">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember"
                                   style="accent-color:#0066ff;width:14px;height:14px;">
                            <span class="text-xs" style="color:#2d4a7a;">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary mt-2" :disabled="!selectedEmail"
                            :class="!selectedEmail ? 'opacity-50 cursor-not-allowed' : ''">
                        <span class="material-symbols-outlined text-[17px]">login</span>
                        Masuk Command Center
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="mt-6 text-center">
                <p class="text-[10px]" style="color:#0f2040;">
                    PT Blue Bird Group · B2B Fleet CRM · Jakarta 2026
                </p>
            </div>
        </div>
    </div>

</body>
</html>
