{{-- The confirmation of a deletion, as Icms\Core\Message::confirm() shows it; posts the confirmed deletion with a security token. --}}
@props(['question', 'action', 'hidden', 'token', 'submit', 'cancel'])
<div class="confirmMsg">
    <h4>{!! $question !!}</h4>
    <form method="post" action="{{ $action }}">
        @foreach ($hidden as $name => $value)
            <x-ipf::hidden :name="$name" :value="$value" />
        @endforeach
        {{ $token }}
        <input type="submit" name="confirm_submit" value="{{ $submit }}" />
        <input type="button" name="confirm_back" value="{{ $cancel }}" onclick="history.go(-1);" />
    </form>
</div>
