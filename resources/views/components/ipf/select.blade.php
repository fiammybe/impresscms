{{-- A single choice; the options come from the field's provider, in its order. --}}
@props(['field'])
<select name="{{ $field['name'] }}" id="{{ $field['name'] }}">
    @foreach ($field['options'] as $value => $label)
        <option value="{{ $value }}" @selected((string) $value === (string) $field['value'])>{{ $label }}</option>
    @endforeach
</select>
