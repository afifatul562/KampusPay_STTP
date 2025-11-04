@props([
    'type' => 'button',
    'variant' => 'primary', // primary, success, danger, warning, indigo, secondary
    'size' => 'md', // sm, md, lg
    'loading' => false,
    'loadingText' => 'Memproses...',
])

@php
    $baseClasses = 'inline-flex items-center justify-center border border-transparent font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ][$size];
    $variantClasses = [
        'primary' => 'text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:ring-primary-500',
        'success' => 'text-white bg-gradient-to-r from-success-600 to-success-700 hover:from-success-700 hover:to-success-800 focus:ring-success-500',
        'danger' => 'text-white bg-gradient-to-r from-danger-600 to-danger-700 hover:from-danger-700 hover:to-danger-800 focus:ring-danger-500',
        'warning' => 'text-white bg-gradient-to-r from-warning-600 to-warning-700 hover:from-warning-700 hover:to-warning-800 focus:ring-warning-500',
        'indigo' => 'text-indigo-700 bg-gradient-to-r from-indigo-100 to-indigo-200 hover:from-indigo-200 hover:to-indigo-300 focus:ring-indigo-500',
        'secondary' => 'text-gray-700 bg-white hover:bg-gray-50 focus:ring-primary-500 border-gray-300',
    ][$variant];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "{$baseClasses} {$sizeClasses} {$variantClasses}", 'disabled' => $loading ? 'disabled' : false]) }}
    @if($loading) disabled @endif>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ $loadingText }}
    @else
        {{ $slot }}
    @endif
</button>

