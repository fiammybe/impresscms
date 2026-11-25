{{-- One field of an IPF form, by its control. A control without a component is refused, never shown as a text input. --}}
@props(['field'])
@switch($field['control'])
    @case('hidden')
        <x-ipf::hidden :name="$field['name']" :value="$field['value']" />
        @break
    @default
        <x-ipf::row :field="$field">
            @switch($field['control'])
                @case('text')
                    <x-ipf::text :field="$field" />
                    @break
                @case('integer')
                    <x-ipf::integer :field="$field" />
                    @break
                @case('textarea')
                    <x-ipf::textarea :field="$field" />
                    @break
                @case('select')
                    <x-ipf::select :field="$field" />
                    @break
                @case('selectmulti')
                    <x-ipf::multiselect :field="$field" />
                    @break
                @case('permission')
                    <x-ipf::permission :field="$field" />
                    @break
                @case('readonly')
                @case('label')
                    <x-ipf::readonly :field="$field" />
                    @break
                @default
                    @php throw new \LogicException('The IPF control "' . $field['control'] . '" of "' . $field['name'] . '" has no Blade component; render this resource with the legacy renderer.'); @endphp
            @endswitch
        </x-ipf::row>
@endswitch
