@props(['field'])
<input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] }}" size="{{ $field['settings']['size'] ?? 50 }}" maxlength="{{ $field['settings']['maxlength'] ?? 255 }}" value="{{ $field['value'] }}" @if ($field['required']) required @endif />
