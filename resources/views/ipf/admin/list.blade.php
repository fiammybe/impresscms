{{-- An admin list: create button, quick search, rows and pages. --}}
<div class="ipf-admin-list">
    @if ($create)
        <div style="margin-bottom: 12px;"><a class="formButton ipf-create" href="{{ $create['url'] }}">{{ $create['label'] }}</a></div>
    @endif
    @if ($search)
        <x-ipf::quicksearch :search="$search" />
    @endif
    <x-ipf::pagination :pages="$pages" />
    <x-ipf::list-table :columns="$columns" :rows="$rows" :actions-label="$actionsLabel" :empty="$empty" />
    <x-ipf::pagination :pages="$pages" />
</div>
