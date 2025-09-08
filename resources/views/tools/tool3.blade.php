@extends('layout')

@section('title') @parent Вычесть элементы из списка @endsection

@section('content')
<div class="tab">
<div class="title">Вычесть элементы из списка</div>
<div class="content">
@include('_ScriptDesk')


<form action="{{route('tool3Post')}}" method="post" class="mb-4">
@csrf



<div class='row'>
	<div class='col-md-6'> 
    <label class='label'>Список 1:</label> 
        <div class="form-group mb-3">
            <textarea class="output-panel form-control @error('users1') is-invalid @enderror"  name="users1" rows="7" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users1']) ? $request['users1'] : null }}</textarea>
            @error('users1') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class='col-md-6'> 
        <div class="form-group mb-3">
        <label class='label'>Список 2:</label> 
            <textarea class="output-panel form-control @error('users2') is-invalid @enderror"  name="users2" rows="7" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users2']) ? $request['users2'] : null }}</textarea>
            @error('users2') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
</div>
</div>

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать"  onclick="change()">
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
