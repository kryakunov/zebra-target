@extends('layout')

@section('title') @parent Расширенный фильтр пользователей @endsection

@section('content')
<div class="tab">
<div class="title">Расширенный фильтр пользователей</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.session')


<form action="{{route('extfilterStore')}}" method="post" class="mb-4">
@csrf

<label class='label'>Вставьте список пользователей:</label> 
<div class="form-group mb-3">       
    <textarea class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="5" placeholder="Вставьте сюда ID пользователей">{{isset($request['users']) ? $request['users'] : null }}</textarea>
    @error('users') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


<div class="input2-group-append">

<!--
<label class='label'><b>Количество фотоальбомов:</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="photoalbums_ot" class="textbox" placeholder=""  value="{{ old('photoalbums_ot') }}"  /> 
    до <input type="text" size="6" name="n_comphotoalbums_do" class="textbox" placeholder=""  value="{{ old('photoalbums_do') }}"  /> 
</div> <br>

<label class='label'><b>Количество видеозаписей:</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="photoalbums_ot" class="textbox" placeholder=""  value="{{ old('photoalbums_ot') }}"  /> 
    до <input type="text" size="6" name="n_comphotoalbums_do" class="textbox" placeholder=""  value="{{ old('photoalbums_do') }}"  /> 
</div> <br>

<label class='label'><b>Количество аудиозаписей:</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="photoalbums_ot" class="textbox" placeholder=""  value="{{ old('photoalbums_ot') }}"  /> 
    до <input type="text" size="6" name="n_comphotoalbums_do" class="textbox" placeholder=""  value="{{ old('photoalbums_do') }}"  /> 
</div> <br>
<label class='label'><b>Количество сообществ:</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="d" class="textbox" placeholder=""  value="{{ old('df') }}"  /> 
    до <input type="text" size="6" name="d" class="textbox" placeholder=""  value="{{ old('df') }}"  /> 
</div> <br>

<label class='label'><b>Количество подписок пользователя (на кого пользователь подписан):</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="photoalbums_ot" class="textbox" placeholder=""  value="{{ old('photoalbums_ot') }}"  /> 
    до <input type="text" size="6" name="n_comphotoalbums_do" class="textbox" placeholder=""  value="{{ old('photoalbums_do') }}"  /> 
</div> <br>
-->
<label class='label'><b>Количество фотографий:</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="photos_min" class="textbox" placeholder=""  value="{{isset($request['photos_min']) ? $request['photos_min'] : null }}"  /> 
    до <input type="text" size="6" name="photos_max" class="textbox" placeholder=""  value="{{isset($request['photos_max']) ? $request['photos_max'] : null }}"  /> 
</div> <br>

<label class='label'><b>Количество друзей:</b></label><br>
<div class="result_format">
    от <input type="text" size="6" name="friends_min" class="textbox" placeholder=""  value="<?php if(isset($_POST['friends_min'])) echo $_POST['friends_min']; ?>"  /> 
    до <input type="text" size="6" name="friends_max" class="textbox" placeholder=""  value="<?php if(isset($_POST['friends_max'])) echo $_POST['friends_max']; ?>"  /> 
</div> <br>


<label class='label'><b>Количество объектов в блоке «Интересные страницы»:</b></label><br>
<div class="result_format">
от <input type="text" size="6" name="subscriptions_min" class="textbox" placeholder=""  value="<?php if(isset($_POST['subscriptions_min'])) echo $_POST['subscriptions_min']; ?>"  /> 
    до <input type="text" size="6" name="subscriptions_max" class="textbox" placeholder=""  value="<?php if(isset($_POST['subscriptions_max'])) echo $_POST['subscriptions_max']; ?>"  /> 
</div> <br>

<label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br>
	     <input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Начать фильтрацию данных"  onclick="change()">
      
    </div>


</form> 
<br>





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