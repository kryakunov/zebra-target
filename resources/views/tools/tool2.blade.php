@extends('layout')

@section('title') @parent Поиск общих элементов @endsection

@section('content')
<div class="tab">
<div class="title">Поиск общих элементов</div>
<div class="content">
@include('_ScriptDesk')


<form action="{{route('tool2Post')}}" method="post" class="mb-4">
@csrf

<label class='label'>Вставьте список пользователей:</label> 

<div class='row'>
	<div class='col-md-6'> 
        <div class="form-group mb-3">
            <textarea class="output-panel form-control @error('users1') is-invalid @enderror"  name="users1" rows="7" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users1']) ? $request['users1'] : null }}</textarea>
            @error('users1') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class='col-md-6'> 
        <div class="form-group mb-3">
            <textarea class="output-panel form-control @error('users2') is-invalid @enderror"  name="users2" rows="7" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users2']) ? $request['users2'] : null }}</textarea>
            @error('users2') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
</div>
</div>

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск"  onclick="change()">
</form> 


@include('errors.exceptions')
@include('errors.session')




@if(isset($data))
    <div class="alert alert-success">
        Найдено <b>{{count($data)}}</b> общих элементов
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif


@endsection
