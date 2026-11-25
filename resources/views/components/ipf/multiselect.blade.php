{{-- Several choices, posted as name[]; nothing is posted when none is selected, which keeps the stored value (legacy). --}}
@props(['field'])
@php $selected = array_map('strval', (array) $field['value']); @endphp
<select size="{{ $field['settings']['size'] ?? 5 }}" name="{{ $field['name'] }}[]" id="{{ $field['name'] }}" multiple="multiple">
    @foreach ($field['options'] as $value => $label)
        <option value="{{ $value }}" @selected(in_array((string) $value, $selected, true))>{{ $label }}</option>
    @endforeach
</select>
