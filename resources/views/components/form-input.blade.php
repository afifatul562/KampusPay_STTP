@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'required' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <div class="text-gray-400">
                    {!! $icon !!}
                </div>
            </div>
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                {{ $attributes->merge(['class' => 'block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500' . ($icon ? ' pl-10' : '')]) }}
                @if($required) required @endif
            />
        @else
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                {{ $attributes->merge(['class' => 'block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500']) }}
                @if($required) required @endif
            />
        @endif
    </div>

    @if($help)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif

    @if($error)
        <p class="mt-1 text-sm text-danger-600">{{ $error }}</p>
    @endif
</div>

