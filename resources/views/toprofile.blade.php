@extends('layout')

@section('title') @parent Работа с профилями @endsection

@section('content')
<div class="tab">
<div class="title">Работа с профилями</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')


<a class="btn btn-outline-success btn-sm" href="{{route('cloud.index')}}"><< Назад</a>
<a class="btn btn-outline-dark btn-sm" href="{{route('getblacklist')}}">Черный список</a><br><br>

<form action="{{route('toprofilestore')}}" method="post">
@csrf


<label class='label margin'><b>Выберите список:</b></label><br>
<select name="list" class="form-control" id="dark">
	@foreach($lists as $list)
		<option value="{{$list['track_id']}}" <?php if(isset($sid) and $sid == $list['track_id']) echo 'selected';?>>{{$list['name']}}</option>
	@endforeach
</select>

<label class='label margin'><b>Сколько человек отобразить из списка?</b></label><br>
<input type="text" name="count"  class="textbox label" placeholder=""  value="<?php if(isset($_POST['count'])) echo $_POST['count']; else echo '10';?>" aria-describedby="button-addon2" /> </label>

<br><br>
<input type="submit" class="btn btn-success btn_size"  value="Получить пользователей">
</form>

<br><br><br>

<table class='table'>
<thead>
	<tr>
        <th colspan='2' class='ShowGroupName'>Профиль</th>
        <th class='ShowGroupName'>Действие</th>
	</tr>
</thead>
<tbody>
@if(isset($data))
   @foreach($data as $value)
	<tr>
        <td><img src="{{$value['photo_50']}}" class='circle' width='30' height='30'></td>
		<td>{{$value['first_name'].' '.$value['last_name']}}</td>
		<td><a href=https://vk.com/id{{$value['id']}} target=_blank class='btn btn-outline-success btn-sm' id='btn-views'>Перейти на страницу</a></td>
    </tr>
	@endforeach


</tbody>
</table>


<form method="post" action="{{ route('toprofiledestroy') }}">
    @csrf

    <input type="hidden" name="count" value="{{$count}}">
    <input type="hidden" name="id" value="{{$sid}}">

    <input type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')" value="Удалить всех из облака">
</form>
@endif
@endsection
