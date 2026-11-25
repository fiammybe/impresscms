{{-- The groups holding a handler permission, posted as name[]; selecting none removes every group (legacy). --}}
@props(['field'])
@php $selected = array_map('strval', (array) $field['value']); @endphp
<select size="4" name="{{ $field['name'] }}[]" id="{{ $field['name'] }}" multiple="multiple" class="ipf-permission">
    @foreach ($field['options'] as $group => $label)
        <option value="{{ $group }}" @selected(in_array((string) $group, $selected, true))>{{ $label }}</option>
    @endforeach
</select>
