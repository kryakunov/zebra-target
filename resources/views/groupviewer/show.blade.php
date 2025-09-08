@extends('groupviewer.layout')

@section('title') @parent Group Viewer @endsection

@section('content')
<div class="tab">
<div class="title">Group Viewer</div>
<div class="content">

{!! $text !!}

@if(isset($repost))
<div style="margin:20px">
    <img src="{{ $repost['photo_100'] }}" height="50" width="50" class="circle" style="margin-bottom:5px; float:left; margin-right: 15px;">
    <b>{{ $repost['first_name'] . ' ' . $repost['last_name'] }}</b> {{ $date }} 
    <br>
    {!! $repost['text'] !!}
</div>
@endif
    <hr>

    <a href="{{ $link }}" target="_blank" class="btn btn-outline-dark btn-sm">Ссылка на пост</a> 

    <a href="{{ route('groupviewer', [--$offset]) }}" class="btn btn-outline-dark btn-sm">< Предыдущий</a> 

    <a href="{{ route('groupviewer', [ $offset + 2]) }}" class="btn btn-outline-dark btn-sm">Следующий ></a> 

</div>
</div> 

@endsection