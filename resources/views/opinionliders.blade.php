
@extends('layout')

@section('content')
<div class="tab">
<div class="title">Лидеры мнений</div>
<div class="content">

@include('_ScriptDesk')
@include('partials.tool-guest-start')

@include('errors.exceptions')
@include('errors.session')

<form action="{{route('opinionliders')}}" method="post" class="mb-4"> 
@csrf

@if(isset($work))
	@include('_work')
@else
 
<label class='label'>Список пользователей:</label> 
    <textarea  class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="8" placeholder="По одному ID в строке">{{isset($request['users']) ? $request['users'] : null }}</textarea>
    @error('users') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
<br>
@endif

<label class='label'>Собирать только тех, на кого подписано не менее </label>
<input type="text" size="6" name="count" class="textbox" placeholder=""  value="<?php if(isset($request['count'])) echo $request['count']; ?>" > людей из списка
<br><br> 

<label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>


	<input class="btn btn-success btn_size" type="submit" id="btnMenu"value=" Начать поиск "  onclick="change()">
</form> 
<br>

@include('partials.tool-guest-end')
@endsection