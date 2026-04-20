@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-5 xl:p-6">
    {{-- Baris judul + aksi: tetap kolom sampai xl agar judul punya lebar penuh (tablet / iPad tidak terjepit) --}}
    <div class="flex flex-col gap-3 sm:gap-4 xl:flex-row xl:items-start {{ isset($actions) ? 'xl:justify-between' : '' }} xl:gap-6">
        <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 flex-1 xl:min-w-[12rem]">
            @if($icon)
                <div class="bg-gradient-to-br from-primary-100 to-primary-200 p-2 sm:p-2.5 xl:p-3 rounded-lg shadow-sm shrink-0 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] sm:[&_svg]:w-5 sm:[&_svg]:h-5 xl:[&_svg]:w-7 xl:[&_svg]:h-7">
                    <div class="text-primary-700 flex items-center justify-center">
                        {!! $icon !!}
                    </div>
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <h1 class="text-fluid-page xl:text-fluid-2xl font-bold text-gray-900 [overflow-wrap:anywhere]">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-fluid-sm text-gray-500 mt-1 leading-snug">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @isset($actions)
            <div class="flex flex-col md:flex-row md:flex-wrap xl:flex-nowrap items-stretch gap-2 w-full xl:w-auto xl:max-w-[min(100%,42rem)] xl:shrink-0 [&_button]:w-full md:[&_button]:min-w-[8rem] md:[&_button]:flex-1 xl:[&_button]:w-auto xl:[&_button]:flex-none [&_a]:w-full md:[&_a]:min-w-[8rem] md:[&_a]:flex-1 xl:[&_a]:w-auto xl:[&_a]:flex-none [&_button]:text-fluid-sm xl:[&_button]:text-sm [&_a]:text-fluid-sm xl:[&_a]:text-sm [&_svg]:w-4 [&_svg]:h-4 md:[&_svg]:w-[1.125rem] md:[&_svg]:h-[1.125rem] shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>

