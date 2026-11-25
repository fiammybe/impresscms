@props(['name', 'value' => null])
<input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ is_array($value) ? implode('|', $value) : $value }}" />
