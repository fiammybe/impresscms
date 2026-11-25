@props(['field'])
<input type="text" inputmode="numeric" pattern="-?[0-9]*" name="{{ $field['name'] }}" id="{{ $field['name'] }}" size="{{ $field['settings']['size'] ?? 5 }}" value="{{ $field['value'] }}" @if ($field['required']) required @endif />
