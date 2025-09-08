@extends('layout')

@section('title') @parent Сообщества пользователей @endsection

@section('content')
<div class="tab">
<div class="title">Сообщества пользователей</div>
<div class="content">
@include('_ScriptDesk')
@include('errors.exceptions')
@include('errors.session')


<form action="{{route('usersgroupspost')}}" method="post" class="mb-4">
@csrf

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="form-group mb-3">

@if(isset($work))
	@include('_work')
@else
    <label class='label'>Вставьте список ID пользователей:</label>
    <textarea class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="8" placeholder="По одному ID на строку">{{isset($request['users']) ? $request['users'] : null }}</textarea>
    @error('users')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
@endif
<br>
		<label class='label'><b>Собирать сообщества, в которых участников:</b></label>

    <div class='row'>
        <div class='col-md-3'>
        <input type="text" size="20" name="ot"   class="textbox" placeholder="от"  value="{{ old('ot') }}" aria-describedby="button-addon2" />
        </div><div class='col-md-3'>

        <input type="text" size="20" name="do"  class="textbox" placeholder="до (включительно)"  value="{{ old('do') }}" aria-describedby="button-addon2" />
        </div></div><br>

    <label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>


    <input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск "  onclick="change()">


</div>





@if(isset($data))

    <form action="{{route('usersgroupsgetids')}}" method="post" class="mb-4">
    @csrf

        @foreach($data as $value)
            <input type="hidden" name="{{$value['id']}}" value="{{$value['id']}}">
        @endforeach


        <div class="alert alert-success">
            Найдено: <b>{{count($data)}}</b> <input type="submit" value="Получить ID всех групп">
        </div>

    </form>

    <table class='table'>
    <thead class='table-head'>
        <tr>
            <th>ID</th>
            <th colspan='2'>Сообщество</th>
            <th>Участников</th>
            <th>Совпавших</th>
            <th>%</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $value)
        <tr>
            <td class='ShowGroupDesc'>{{ $value['id'] }}</td>
            <td width='5%'><img src={{$value['photo_50']}} class='circle'></td>
            <td class='ShowGroupName'><a href=http://vk.ru/club{{$value['id']}} target='_blank'> {{ $value['name'] }}</a></td>
            <td class='ShowGroupDesc'> {{ $value['members_count'] }}</td>
            <td class='ShowGroupDesc'> {{ $count[$value['id']] }}</td>
            <td class='ShowGroupDesc'> {{ round(100*$count[$value['id']] / $countUsers) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tbody>

@endif






@endsection
