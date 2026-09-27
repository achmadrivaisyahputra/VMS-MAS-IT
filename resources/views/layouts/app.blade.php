<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - MAS-IT VMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex flex-col md:flex-row overflow-x-hidden">

    <!-- Mobile Top Navigation Header -->
    <div class="md:hidden flex items-center justify-between p-4 bg-gradient-to-r from-[#001233] to-[#0044cc] border-b border-blue-900 sticky top-0 z-40 shadow-lg">
        <div class="flex items-center gap-3">
            <!-- LOGO MAS-IT DI MOBILE -->
            <img src="{{ asset('images/logo-masit.png') }}" alt="MAS-IT Logo" class="h-8 object-contain drop-shadow-md">
            <span class="font-bold text-sm text-white tracking-wide">MAS-IT VMS</span>
        </div>
        <button onclick="toggleMobileSidebar()" class="p-2 text-blue-200 hover:text-white focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
        </button>
    </div>

    <!-- Sidebar Menu (Responsive) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-[#001233] via-[#002266] to-[#0055ff] border-r border-blue-800/50 flex flex-col justify-between shrink-0 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-200 ease-in-out min-h-screen shadow-[4px_0_24px_rgba(0,34,102,0.15)]">
        <div>
            <!-- Brand Logo -->
            <div class="p-5 border-b border-blue-800/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-masit.png') }}" alt="MAS-IT Logo" class="h-10 object-contain drop-shadow-md">
                    <div>
                        <h2 class="font-bold text-base tracking-tight text-white">MAS-IT VMS</h2>
                        <p class="text-[10px] text-blue-300 font-medium tracking-wider uppercase">{{ Auth::user()->role->nama_role ?? 'Pengguna' }}</p>
                    </div>
                </div>
                <button onclick="toggleMobileSidebar()" class="md:hidden text-blue-300 hover:text-white">&times;</button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-medium">
                @php
                    $roleId = Auth::user()->id_role ?? 0;
                    $currentRoute = Route::currentRouteName();
                @endphp

                <!-- Dashboard link per role -->
                @if($roleId == 1)
                    <a href="{{ route('kepala.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'kepala.dashboard') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard Strategis</span>
                    </a>
                @elseif($roleId == 2)
                    <a href="{{ route('pimpinan.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'pimpinan.dashboard') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard Operasional</span>
                    </a>
                @elseif($roleId == 3)
                    <a href="{{ route('engineer.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'engineer.dashboard') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard Penugasan</span>
                    </a>
                @endif

                <div class="pt-4 pb-1 text-[10px] uppercase font-bold text-blue-300/80 tracking-wider">Modul VMS</div>

                <a href="{{ route('kunjungan.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'kunjungan') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Kunjungan Kerja</span>
                </a>

                @if($roleId == 1 || $roleId == 2)
                    <div class="pt-3 pb-1 text-[10px] uppercase font-bold text-blue-300/80 tracking-wider">Master Data</div>
                    <a href="{{ route('master.customer.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'master.customer') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Data Customer</span>
                    </a>
                    <a href="{{ route('master.site.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'master.site') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Data Site</span>
                    </a>
                    <a href="{{ route('master.engineer.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'master.engineer') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Data Engineer</span>
                    </a>
                    <a href="{{ route('master.tool.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'master.tool') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Tools & Alat Kerja</span>
                    </a>
                @endif

                <div class="pt-3 pb-1 text-[10px] uppercase font-bold text-blue-300/80 tracking-wider">Laporan</div>
                <a href="{{ route('laporan.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ str_contains($currentRoute, 'laporan') ? 'bg-white/20 text-white shadow-lg border border-white/10' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan & PDF</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-blue-400/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-transparent border border-blue-200 text-white flex items-center justify-center text-xs font-bold shadow-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->nama }}</p>
                        <p class="text-[10px] text-blue-200 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                
                <!-- Aksi: Profil & Logout -->
                <div class="flex items-center gap-1 shrink-0 ml-2">
                    <a href="{{ route('profile.index') }}" title="Pengaturan Profil" class="p-1.5 text-blue-200 hover:text-white rounded-lg hover:bg-white/10 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-1.5 text-blue-200 hover:text-white rounded-lg hover:bg-white/10 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside> 

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-50 min-h-screen">
        <header class="h-16 border-b border-slate-200 px-4 md:px-8 flex items-center justify-between bg-white sticky top-0 z-20 shadow-sm">
            <div>
                <h1 class="text-sm md:text-base font-bold text-[#002266] truncate">@yield('header_title', 'Sistem Manajemen Kunjungan')</h1>
                <p class="text-[10px] md:text-xs text-slate-500 font-medium">PT MAS-IT Solusi Integrasi</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold">
                <span class="px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-[#003399] flex items-center gap-1.5 text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-[#0044cc] animate-pulse"></span>
                    {{ date('d M Y') }}
                </span>
            </div>
        </header>

        <main class="p-4 md:p-8 flex-1">
            @yield('content')
        </main>
    </div>

    <!-- Global Confirmation Pop-up -->
    <div id="globalConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="w-full max-w-md p-6 bg-white border border-slate-200 rounded-2xl shadow-2xl text-center">
            <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-[#003399] mb-3 border border-blue-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 id="globalConfirmTitle" class="text-base font-bold text-slate-800 mb-2">Konfirmasi Tindakan</h3>
            <p id="globalConfirmMessage" class="text-xs text-slate-500 mb-5 leading-relaxed font-medium">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeConfirmModal()" class="w-1/2 py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">Batal</button>
                <button type="button" id="globalConfirmSubmitBtn" class="w-1/2 py-2 px-4 bg-[#002266] hover:bg-[#001233] text-white rounded-xl text-xs font-bold shadow-lg shadow-blue-900/20 transition">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }

        let targetFormToSubmit = null;
        function showConfirmModal(formElement, title, message) {
            targetFormToSubmit = formElement;
            document.getElementById('globalConfirmTitle').innerText = title || 'Konfirmasi Tindakan';
            document.getElementById('globalConfirmMessage').innerText = message || 'Lanjutkan proses ini?';
            document.getElementById('globalConfirmModal').classList.remove('hidden');
        }
        function closeConfirmModal() {
            document.getElementById('globalConfirmModal').classList.add('hidden');
            targetFormToSubmit = null;
        }
        document.getElementById('globalConfirmSubmitBtn').addEventListener('click', () => {
            if (targetFormToSubmit) targetFormToSubmit.submit();
        });
    </script>
</body>
</html>