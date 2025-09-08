@php
$userInfo = collect([$user->name, $user->country, $user->email])->implode(', ');
@endphp
{{ $userInfo }}
