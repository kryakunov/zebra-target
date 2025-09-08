@extends('layout')

@section('title') @parent Менеджер задач @endsection

@section('content')
<div class="tab">
<div class="title">{{ $work->name }}</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

<a href="{{route('myworks')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a> 
<br><br>

<label>Найдено: <b>{{ $work->count }}</b></label><br><br>
<textarea class="output-panel form-control mb-2"  id="textarea" rows="13">@forelse($data as $value){{$value}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
    <button type="button" class="btn btn-outline-dark "  onclick="copy()">Скопировать</button> <br>


    </div>
</div> 

@endsection



