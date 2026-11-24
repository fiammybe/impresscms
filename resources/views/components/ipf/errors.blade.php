{{-- Validation errors, each naming its field's caption. --}}
@props(['messages' => []])
@if ($messages !== [])
    <div class="errorMsg"><ul>@foreach ($messages as $message)<li>{{ $message }}</li>@endforeach</ul></div>
@endif
