<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>404 — Halaman Tidak Ditemukan | Bluebird CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@400,0&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #09090f; font-family: 'Inter', sans-serif; }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 70% 50% at 50% -10%, rgba(59,130,246,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 110%, rgba(139,92,246,0.05) 0%, transparent 60%);
            pointer-events: none; z-index: 0;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative">
    <div class="relative z-10 text-center max-w-md w-full">

        {{-- Card --}}
        <div class="cc-card p-10 flex flex-col items-center gap-6">

            {{-- Icon --}}
            <div class="w-24 h-24 rounded-2xl flex items-center justify-center text-5xl"
                 style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15);">
                🤔
            </div>

            {{-- Error code --}}
            <div>
                <div class="text-[72px] font-black leading-none tracking-tight"
                     style="background: linear-gradient(135deg, #3b82f6, var(--color-primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    404
                </div>
                <div class="text-[18px] font-bold text-slate-200 mt-2">Halaman Tidak Ditemukan</div>
                <div class="text-[13px] text-slate-500 mt-2 leading-relaxed">
                    Halaman yang kamu cari tidak ada atau sudah dipindahkan.<br>
                    Cek URL atau kembali ke halaman utama.
                </div>
            </div>

            {{-- Divider --}}
            <div class="w-full h-px" style="background: rgba(255,255,255,0.06);"></div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full">
                <a href="{{ url('/') }}" class="btn-primary flex-1 justify-center">
                    <span class="material-symbols-outlined text-[16px]">home</span>
                    Home
                </a>
                <button onclick="history.back()" class="btn-secondary flex-1 justify-center">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Kembali
                </button>
            </div>

        </div>

        {{-- Footer --}}
        <div class="mt-6 text-[11px] text-slate-600">
            Bluebird CRM · Error 404
        </div>

    </div>
</body>
</html>
