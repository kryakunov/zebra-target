@extends('layout')

@section('title') @parent Мои задачи @endsection

@section('content')
<div class="tab">
<div class="title">Мои задачи - Фильтр пользователей</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

<p>
<a href="{{route('profile')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a> 

</p>

<br>
<p>Найдено: <b>{{ count($data) }}</b></p>
    <textarea class="output-panel form-control mb-2"  id="textarea" rows="13">@forelse($data as $value){{$value}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>

    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> 

     
    </div>
</div> 

@endsection



