{{-- The mimetype administration page (db:system_adm_mimetype.html) around a list or a form. --}}
<div class="CPbigTitle" style="background-image: url({{ $siteUrl }}/modules/system/admin/mimetype/images/mimetype_big.png)">{{ $heading }}</div><br />

@if ($explain)
    <h1>{{ $explainTitle }}</h1>
    <p>{!! $explainText !!}</p>
@endif

<p>{{ $title }}</p>

{{ $content }}
