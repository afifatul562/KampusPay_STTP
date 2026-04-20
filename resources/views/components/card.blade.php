@props([
    'title' => null,
    'subtitle' => null,
    'withHeader' => false,
    'withPadding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden']) }}>
    @if($title || $withHeader)
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 {{ $withHeader ? 'bg-gradient-to-r from-gray-50 to-gray-100' : '' }}">
            @if($title)
                <h3 class="text-fluid-lg font-semibold text-gray-800">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-fluid-sm text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
            @isset($header)
                {{ $header }}
            @endisset
        </div>
    @endif

    <div class="{{ $withPadding ? 'p-4 sm:p-5 md:p-6' : '' }}">
        {{ $slot }}
    </div>
</div>

