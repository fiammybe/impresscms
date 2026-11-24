{{-- The caption, required marker and description of a field, and its control. --}}
@props(['field'])
<tr class="ipf-field" data-field="{{ $field['name'] }}">
    <td class="head">
        <label for="{{ $field['name'] }}">{{ $field['caption'] }}</label>@if ($field['required'])<span class="caption-required" title="required">*</span>@endif
        <x-ipf::description :text="$field['description']" />
    </td>
    <td class="even">{{ $slot }}</td>
</tr>
