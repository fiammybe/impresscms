{{-- An IPF form posting the legacy field names to the legacy admin URL. --}}
<form id="{{ $id }}" action="{{ $action }}" method="post" class="ipf-form">
    <table width="100%" class="outer" cellspacing="1">
        <tr><th colspan="2">{{ $caption }}</th></tr>
        @foreach ($fields as $field)
            <x-ipf::field :field="$field" />
        @endforeach
        <tr>
            <td class="head">&nbsp;</td>
            <td class="even">
                @foreach ($hidden as $name => $value)
                    <x-ipf::hidden :name="$name" :value="$value" />
                @endforeach
                {{ $token }}
                <input type="submit" class="formButton" name="submit" id="submit" value="{{ $submit }}" />
            </td>
        </tr>
    </table>
</form>
