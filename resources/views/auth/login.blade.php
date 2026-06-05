<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Golden Bird CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-lg" x-data="loginApp()">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-400 rounded-2xl mb-4 shadow-lg shadow-yellow-500/30">
                <span class="text-3xl">🐦</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Golden Bird CRM</h1>
            <p class="text-slate-400 text-sm mt-1">B2B Fleet Management System — V7.2</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

            {{-- Tab Header --}}
            <div class="flex border-b border-gray-100">
                <button
                    @click="tab = 'demo'"
                    :class="tab === 'demo' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50'"
                    class="flex-1 py-3 text-sm font-semibold transition-colors"
                >
                    ⚡ 1-Click Demo
                </button>
                <button
                    @click="tab = 'manual'"
                    :class="tab === 'manual' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50'"
                    class="flex-1 py-3 text-sm font-semibold transition-colors"
                >
                    🔐 Manual Login
                </button>
            </div>

            <div class="p-8">

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- 1-Click Demo Tab --}}
                <div x-show="tab === 'demo'">
                    <p class="text-sm text-gray-500 mb-4">Klik salah satu role untuk langsung masuk:</p>

                    <div class="grid grid-cols-2 gap-3">

                        {{-- Director --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="director@goldenbird.co.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border-2 border-purple-200 hover:border-purple-500 hover:bg-purple-50 transition-all group text-left">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 group-hover:bg-purple-200 flex items-center justify-center text-lg flex-shrink-0 transition-colors">👔</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">Director</div>
                                    <div class="text-xs text-gray-400">Full access</div>
                                </div>
                            </button>
                        </form>

                        {{-- GM --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="gm@goldenbird.co.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border-2 border-blue-200 hover:border-blue-500 hover:bg-blue-50 transition-all group text-left">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center text-lg flex-shrink-0 transition-colors">🏢</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">GM</div>
                                    <div class="text-xs text-gray-400">General Manager</div>
                                </div>
                            </button>
                        </form>

                        {{-- Manager --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="manager@goldenbird.co.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border-2 border-green-200 hover:border-green-500 hover:bg-green-50 transition-all group text-left">
                                <div class="w-10 h-10 rounded-xl bg-green-100 group-hover:bg-green-200 flex items-center justify-center text-lg flex-shrink-0 transition-colors">📊</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">Manager</div>
                                    <div class="text-xs text-gray-400">Sales Manager</div>
                                </div>
                            </button>
                        </form>

                        {{-- Sales --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="sales1@goldenbird.co.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border-2 border-yellow-200 hover:border-yellow-500 hover:bg-yellow-50 transition-all group text-left">
                                <div class="w-10 h-10 rounded-xl bg-yellow-100 group-hover:bg-yellow-200 flex items-center justify-center text-lg flex-shrink-0 transition-colors">💼</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">Sales</div>
                                    <div class="text-xs text-gray-400">Account Executive</div>
                                </div>
                            </button>
                        </form>

                        {{-- Operational --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="ops@goldenbird.co.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border-2 border-orange-200 hover:border-orange-500 hover:bg-orange-50 transition-all group text-left">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 group-hover:bg-orange-200 flex items-center justify-center text-lg flex-shrink-0 transition-colors">🚗</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">Operational</div>
                                    <div class="text-xs text-gray-400">Fleet Ops</div>
                                </div>
                            </button>
                        </form>

                        {{-- Finance --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="finance@goldenbird.co.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl border-2 border-emerald-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all group text-left">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center text-lg flex-shrink-0 transition-colors">💰</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">Finance</div>
                                    <div class="text-xs text-gray-400">Finance Team</div>
                                </div>
                            </button>
                        </form>

                    </div>

                    <p class="text-center text-xs text-gray-400 mt-4">Semua demo account menggunakan password: <code class="bg-gray-100 px-1.5 py-0.5 rounded font-mono">password123</code></p>
                </div>

                {{-- Manual Login Tab --}}
                <div x-show="tab === 'manual'">
                    @if (session('status'))
                        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
                                placeholder="email@goldenbird.co.id"
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm"
                                placeholder="••••••••"
                            >
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                                Ingat saya
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition duration-200 text-sm"
                        >
                            Masuk
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">
            © 2026 Golden Bird CRM — V7.2 · <span class="text-slate-600">Bluebird Group</span>
        </p>
    </div>

    <script>
        function loginApp() {
            return {
                tab: 'demo'  // Default ke tab 1-click
            }
        }
    </script>

</body>
</html>
