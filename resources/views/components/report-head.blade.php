@props(['title', 'subtitle' => null])

<div class="rp-head">
    <h1>{{ $title }}</h1>
    @if($subtitle)
        <p>{{ $subtitle }}</p>
    @endif
</div>
