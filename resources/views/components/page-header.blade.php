@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div class="bg-white rounded-xl shadow-md border border-gray-200 {{ isset($actions) ? 'p-6' : 'p-6' }}">
    <div class="flex items-center {{ isset($actions) ? 'justify-between' : '' }} gap-4">
        <div class="flex items-center gap-4">
            @if($icon)
                <div class="bg-gradient-to-br from-primary-100 to-primary-200 p-3 rounded-lg shadow-sm">
                    <div class="text-primary-700">
                        {!! $icon !!}
                    </div>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @isset($actions)
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>

