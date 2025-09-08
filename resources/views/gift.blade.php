@extends('layout')

@section('content')

<div class="tab">

<div class="content">
@include('errors.session')


<img src="{{$user['photo_50'] }}" class="circle">
<b>{{  $user['first_name'] . ' ' . $user['last_name'] }},</b> для вас подарок!
</div>
</div>

@endsection