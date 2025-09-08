@extends('layout')

@section('title') @parent Удалить дубли @endsection

@section('content')
<div class="tab">
<div class="title">Удалить дубли</div>
<div class="content">
@include('_ScriptDesk')


<form action="{{route('tool4Post')}}" method="post" class="mb-4">
@csrf

<label class='label'>Вставьте список элементов:</label> 
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="7" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users']) ? $request['users'] : null }}</textarea>
    @error('users') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Удалить дубли"  onclick="change()">
</form> 


@include('errors.exceptions')
@include('errors.session')



@if(isset($data))
    <div class="alert alert-success">
        В финальном списке: <b>{{count($data)}}</b>  элементов
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif



@endsection
