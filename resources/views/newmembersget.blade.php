@extends('layout')

@section('content')
<div class="tab">
<div class="title">Отслеживание новых вступлений в группы</div>
<div class="content">
<h6>Как работает этот скрипт?</h6>
<p class="ScriptDesc">
Дообавьте группы в отслеживание и находите новых подписчиков <a href="#">Видео-туториал</a>
</p>


@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

        <a class="btn btn-outline-success btn-sm" href="{{route('NewMembersShow') }}"><< Назад</a>
        <a class="btn btn-outline-danger btn-sm" href="{{route('NewMembersShowDelete', ['id' => $groupId]) }}" onclick="return confirm('Вы уверены?')">Удалить пользователей</a>
        <br><br>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@forelse($data as $id){{$id}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>

<button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> 
    </div>
</div> 

@endsection