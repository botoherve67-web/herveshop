{{ $title }}

{{ $greeting }}

{{ $intro }}

@foreach ($details ?? [] as $label => $value)
{{ $label }} : {{ $value }}
@endforeach

@if (!empty($actionUrl) && !empty($actionText))
{{ $actionText }} : {{ $actionUrl }}
@endif

{{ $closing }}

HerveShop
