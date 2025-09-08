@extends('layout')

@section('title') @parent Сбор друзей и подписчиков @endsection

@section('content')
<div class="tab">
<div class="title">Сбор друзей и подписчиков</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.session')


<form action="{{route('getfriends')}}" method="post" class="mb-4">
@csrf
        @if(isset($work))
            @include('_work')
        @else
        
        <label class='label'>Список пользователей:</label> 


            <textarea  class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="5" placeholder="По одному ID в строке">{{isset($request['users']) ? $request['users'] : ''}}{{ old('users') }}</textarea>
            @error('users') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

        @endif


<label class='label'><b>Кого собираем?</b></label> 
<div class="result_format"> 
    <div class="filtr_param">
        <label class="label-checkbox"><input type="checkbox" class="checkbox" name="friends" value="checked"   {{isset($request['friends']) ? 'checked' : null }}>Друзей</label><br>
        <label class="label-checkbox"><input type="checkbox" class="checkbox" name="followers" value="checked"   {{isset($request['followers']) ? 'checked' : null }}>Подписчиков</label>
    </div>
</div>



<label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br>

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск "  onclick="change()">
</form>


@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif


@endsection