{{-- The rows of an admin list: sortable column headers, values, row actions. --}}
@props(['columns', 'rows', 'actionsLabel', 'empty'])
<table width="100%" cellspacing="1" cellpadding="3" border="0" class="outer ipf-list">
    <tr>
        @foreach ($columns as $column)
            <th @if ($column['width']) width="{{ $column['width'] }}" @endif align="{{ $column['align'] }}">
                <strong>@if ($column['url'])<a href="{{ $column['url'] }}">{{ $column['caption'] }}</a>@else{{ $column['caption'] }}@endif</strong>
            </th>
        @endforeach
        <th width="60" align="center"><strong>{{ $actionsLabel }}</strong></th>
    </tr>
    @forelse ($rows as $row)
        <tr class="{{ $loop->odd ? 'odd' : 'even' }}" data-id="{{ $row['id'] }}">
            @foreach ($row['cells'] as $index => $cell)
                <td align="{{ $columns[$index]['align'] }}">{{ $cell }}</td>
            @endforeach
            <td align="center"><x-ipf::row-actions :actions="$row['actions']" /></td>
        </tr>
    @empty
        <tr><td class="head" colspan="{{ count($columns) + 1 }}" align="center">{{ $empty }}</td></tr>
    @endforelse
</table>
