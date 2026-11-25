@props(['field'])
<textarea name="{{ $field['name'] }}" id="{{ $field['name'] }}" rows="{{ $field['settings']['rows'] ?? 5 }}" cols="{{ $field['settings']['cols'] ?? 60 }}" @if ($field['required']) required @endif>{{ $field['value'] }}</textarea>
