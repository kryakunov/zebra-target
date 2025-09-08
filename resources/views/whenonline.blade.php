@extends('layout')

@section('title') @parent Отслеживание когда был онлайн @endsection

@section('content')
<div class="tab">
<div class="title">Отслеживание когда был онлайн</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

    @foreach($data as $key => $value)

        <hr><h5>{{ $key }}</h5>

            @foreach($value as $item)

              {{ $item }}</br>
            @endforeach

    @endforeach


</div>
</div>



@endsection
