@props(['pages' => []])
@if ($pages !== [])
    <div class="pagination default">
        @foreach ($pages as $page)
            @if ($page['current'])
                <span class="current"><strong>({{ $page['number'] }})</strong></span>
            @else
                <a href="{{ $page['url'] }}">{{ $page['number'] }}</a>
            @endif
        @endforeach
    </div>
@endif
