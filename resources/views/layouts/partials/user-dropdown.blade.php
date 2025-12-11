<div class="flex items-center gap-4">
    <span class="hidden sm:inline-block px-3 py-1 text-sm font-semibold text-white rounded-full
        @switch(Auth::user()->role)
            @case('admin') bg-danger-500 @break
            @case('kasir') bg-primary-500 @break
            @case('mahasiswa') bg-success-500 @break
        @endswitch
    ">
        {{ ucfirst(Auth::user()->role) }}
    </span>

    {{-- Dropdown Menu untuk User --}}
    <div x-data="{ open: false }" class="relative">
        @php
            $initials = strtoupper(substr(Auth::user()->nama_lengkap, 0, 1) . (strlen(Auth::user()->nama_lengkap) > 1 ? substr(strstr(Auth::user()->nama_lengkap, ' '), 1, 1) : ''));
            if (empty(trim($initials))) {
                $initials = strtoupper(substr(Auth::user()->nama_lengkap, 0, 2));
            }
            $roleColors = [
                'admin' => ['from' => 'from-red-400', 'to' => 'to-red-600'],
                'kasir' => ['from' => 'from-blue-400', 'to' => 'to-blue-600'],
                'mahasiswa' => ['from' => 'from-green-400', 'to' => 'to-green-600']
            ];
            $avatarColors = $roleColors[Auth::user()->role] ?? $roleColors['mahasiswa'];
        @endphp
        <button @click="open = !open" class="flex items-center gap-3 text-sm text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg px-2 py-1.5 transition-all">
            <div class="relative">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $avatarColors['from'] }} {{ $avatarColors['to'] }} flex items-center justify-center text-white font-semibold text-sm shadow-md ring-2 ring-white">
                    {{ $initials }}
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-success-500 rounded-full border-2 border-white"></div>
            </div>
            <div class="hidden md:block text-left">
                <div class="font-medium text-gray-900">{{ Auth::user()->nama_lengkap }}</div>
                <div class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
            <svg class="ml-1 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="open" @click.away="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50"
                style="display: none;">

            {{-- User Info Header --}}
            <div class="px-4 py-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $avatarColors['from'] }} {{ $avatarColors['to'] }} flex items-center justify-center text-white font-semibold text-sm shadow-sm">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900 truncate">{{ Auth::user()->nama_lengkap }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ Auth::user()->username }}</div>
                    </div>
                </div>
            </div>

            <div class="py-1">
                @if(Auth::user()->isMahasiswa() || Auth::user()->isKasir())
                    @if(Auth::user()->isMahasiswa())
                        <a href="{{ route('mahasiswa.profil') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profil</span>
                        </a>
                        <a href="{{ route('mahasiswa.profil.password.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            <span>Ubah Password</span>
                        </a>
                        <a href="{{ route('mahasiswa.aktivasi') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Aktivasi</span>
                        </a>
                    @elseif(Auth::user()->isKasir())
                        <a href="{{ route('kasir.pengaturan.password') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            <span>Ubah Password</span>
                        </a>
                    @endif
                    <div class="border-t border-gray-100 my-1"></div>
                @endif

                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-danger-600 hover:bg-danger-50 transition-colors">
                        <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const logoutForm = document.getElementById('logout-form');
                        if (logoutForm) {
                            logoutForm.addEventListener('submit', function(e) {
                                // Pastikan CSRF token ada
                                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                if (csrfToken) {
                                    const csrfInput = logoutForm.querySelector('input[name="_token"]');
                                    if (csrfInput) {
                                        csrfInput.value = csrfToken;
                                    }
                                }
                            });
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</div>
