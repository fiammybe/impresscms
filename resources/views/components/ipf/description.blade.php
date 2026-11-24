{{-- A field description: a language string of the definition, which may hold markup (as in the legacy form). --}}
@props(['text' => null])
@if ($text !== null && $text !== '')
    <br /><span class="ipf-description">{!! $text !!}</span>
@endif
