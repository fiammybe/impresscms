{{-- List filters (#[ListFilter]): one select per filter, submitted with the list's parameters. --}}
@props(['filters' => [], 'action' => '', 'hidden' => []])
@if ($filters !== [])
    <form method="get" action="{{ $action }}" class="ipf-filters">
        @foreach ($hidden as $name => $value)
            <x-ipf::hidden :name="$name" :value="$value" />
        @endforeach
        @foreach ($filters as $filter)
            <label for="filter_{{ $filter['field'] }}">{{ $filter['caption'] }}</label>
            <select name="filtersel" id="filter_{{ $filter['field'] }}" onchange="this.form.submit()">
                @foreach ($filter['options'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) $value === (string) $filter['value'])>{{ $label }}</option>
                @endforeach
            </select>
        @endforeach
    </form>
@endif
