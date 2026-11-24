{{-- A read-only field: shown, never posted. --}}
@props(['field'])
<span class="ipf-readonly" id="{{ $field['name'] }}">{{ is_array($field['value']) ? implode(', ', $field['value']) : $field['value'] }}</span>
