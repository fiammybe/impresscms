{{-- The quick search of a list, under the parameter name of Icms\Ipf\View\Table (quicksearch_<Entity>). --}}
@props(['search'])
<form method="get" action="{{ $search['action'] }}" class="ipf-quicksearch">
    @foreach ($search['hidden'] as $name => $value)
        <x-ipf::hidden :name="$name" :value="$value" />
    @endforeach
    <label for="{{ $search['name'] }}"><strong>{{ $search['label'] }}:</strong></label>
    <input type="text" id="{{ $search['name'] }}" name="{{ $search['name'] }}" size="15" maxlength="255" value="{{ $search['value'] }}" />
    <input type="submit" value="{{ $search['submit'] }}" />
</form>
