@extends('layout')

@section('title') @parent Топ подписчики @endsection

@section('content')
<div class="tab">
<div class="title">Топ подписчики</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.session')

<form action="{{route('topfollowers')}}" method="post" class="mb-4">
@csrf

<div class="form-group mb-3">  

    <label class='label'><b>Вставьте список пабликов:</b></label>   
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror"  name="groups" rows="5" placeholder="По одному ID на строку">{{ old('groups') }}</textarea>
    @error('groups') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <br>

    <div class="row">
        <div class="col-md-6">
                <label class='label margin'><b>Среди скольких первых подписок паблик должен быть у пользователя?</b></label>
                <div class="row">
                <div class="col-md-6">
                    <input type="text" size="20" name="top"  class="textbox" placeholder="до"  value="{{ old('top')  }}" aria-describedby="button-addon2" /> 
                </div>
                </div>
        </div>
    </div><br>
    <label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br>
    <br>
    <input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск "  onclick="change()">

    
</div>


@include('errors.exceptions')
@include('errors.session')



@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>


@endif

@if(isset($error))
    <div class="alert alert-success">
        Найдено: <b>{{count($error)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($error as $err)
{{$err}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>


@endif




@endsection