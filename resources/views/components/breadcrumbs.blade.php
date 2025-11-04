@props([
    'items' => [],
])

@if(count($items) > 0)
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if($index > 0)
                        <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @endif

                    @if(isset($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-primary-600 transition-colors">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="{{ $loop->last ? 'text-gray-900 font-semibold' : '' }}">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

