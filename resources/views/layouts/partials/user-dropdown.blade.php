<div class="flex items-center gap-4">
    <span class="hidden sm:inline-block px-3 py-1 text-sm font-semibold text-white rounded-full
        @switch(Auth::user()->role)
            @case('admin') bg-red-500 @break
            @case('kasir') bg-blue-500 @break
            @case('mahasiswa') bg-green-500 @break
        @endswitch
    ">
        {{ ucfirst(Auth::user()->role) }}
    </span>

    {{-- Dropdown Menu untuk User --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center text-sm text-gray-600 focus:outline-none">
            <span>Halo, {{ Auth::user()->nama_lengkap }}</span>
            <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10"
                style="display: none;">

            @if(Auth::user()->isMahasiswa() || Auth::user()->isKasir())
                @if(Auth::user()->isMahasiswa())
                    <a href="{{ route('mahasiswa.profil') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                    <a href="{{ route('mahasiswa.profil.password.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ubah Password</a>
                @elseif(Auth::user()->isKasir())
                    <a href="{{ route('kasir.pengaturan.password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ubah Password</a>
                @endif
                <div class="border-t border-gray-100"></div>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
