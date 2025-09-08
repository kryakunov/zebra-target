@extends('layout')

@section('title') @parent Мои задачи @endsection

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

<form action="{{route('usersgroupspost')}}" method="post" class="mb-4">
@csrf
<!--
    <label class='label'>
    <b>Что делаем с полученными данными?</b>
    </label><br>
    <select name="next" class="textbox" style="font-size: 14px">
        <option value="usersfilter">Отфильтровать пользователей</option>
        <option value="liders">Найти лидеров мнений</option>
        <option value="usersfilter">Найти сообщества пользователей</option>
    </select>


    <input class="btn btn-outline-dark btn-sm" type="submit" id="btnMenu" value=" Перейти "  onclick="change()">
</form>

    <a href="{{route('movework', ['id' => $work['id'], 'work' => 'filter'])}}" class="submitbutton" >Отфильтровать пользователей</a> 


<br>

<div class="row">
    <div class="col-md-4">

<p>В исходном задании: <b>{{ ($request['groups']) }}</b></p>
</div>
<div class="col-md-4">



</div>
<div class="col-md-4">
<p>В итоговом списке: <b>{{ $work->count }}</b></p>

</div>
</div>
-->
Просмотрено групп: <b>{{ count($groups) }}</b><br>
<label>Найдено: <b>{{ count($data) }}</b> пользователей</label> 
<textarea class="output-panel form-control mb-2"  id="textarea" rows="13">@forelse($data as $value){{$value}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
    <button type="button" class="btn btn-outline-dark "  onclick="copy()">Скопировать</button> <br>


    </div>
</div> 

@endsection



