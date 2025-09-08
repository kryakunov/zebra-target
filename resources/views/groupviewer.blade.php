@extends('layout')

@section('title') @parent Group Viewer @endsection

@section('content')
<div class="tab">
<div class="title">Group Viewer</div>
<div class="content">
fds
{!! $text !!}

{!! $text2 !!}

    <hr>

    {{ $date }} <a href="{{ $link }}" target="_blank" class="btn btn-outline-dark btn-sm">Ссылка на пост</a> 

    <a href="{{ route('groupviewer', [--$offset]) }}"  class="btn btn-outline-dark btn-sm">< Предыдущий</a> 

    <a href="{{ route('groupviewer', [ $offset + 2]) }}"  class="btn btn-outline-dark btn-sm">Следующий ></a> 

</div>
</div> 

@endsection