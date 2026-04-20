@props([
    'headers' => [],
    'emptyMessage' => 'Tidak ada data untuk ditampilkan.',
    'emptyIcon' => null,
])

<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    @isset($title)
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="text-fluid-lg font-semibold text-gray-800">{{ $title }}</h3>
        </div>
    @endisset

    <div class="overflow-x-auto overscroll-x-contain -mx-px touch-pan-x">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }} aria-label="{{ $attributes->get('aria-label', 'Tabel data') }}">
            @if(count($headers) > 0)
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($headers as $header)
                            <th class="px-3 sm:px-6 py-2.5 sm:py-3 text-left text-fluid-xs sm:text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="bg-white divide-y divide-gray-200 text-fluid-sm sm:text-sm">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @isset($empty)
        <div class="px-4 sm:px-6 py-8 sm:py-10">
            {{ $empty }}
        </div>
    @endisset

    @isset($pagination)
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 overflow-x-auto">
            {{ $pagination }}
        </div>
    @endisset
</div>

