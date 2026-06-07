@props(['items' => []])

<nav class="flex items-center text-sm text-gray-500 mb-6 space-x-1">
    @foreach($items as $i => $item)
        @if($i < count($items) - 1)
            <a href="{{ $item['url'] }}" class="hover:text-blue-600 transition-colors">{{ $item['label'] }}</a>
            <span class="text-gray-300">/</span>
        @else
            <a href="{{ $item['url'] ?? '#' }}" class="text-gray-800 font-medium hover:text-blue-600 transition-colors">{{ $item['label'] }}</a>
        @endif
    @endforeach
</nav>
