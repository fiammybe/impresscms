@props(['actions' => []])
@foreach ($actions as $action)
    <a href="{{ $action['url'] }}" class="ipf-action ipf-action-{{ $action['name'] }}" title="{{ $action['label'] }}">{{ $action['label'] }}</a>
@endforeach
