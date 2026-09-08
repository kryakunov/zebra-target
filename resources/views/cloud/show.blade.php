@extends('layout')

@section('title') @parent Облако @endsection

@section('content')
<div class="tab">
<div class="title">Облако</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

    <a class="btn btn-outline-success btn-sm" href="{{route('cloud.index')}}"><< Назад</a>
    <br><br>
    <h4>{{ $name }} </h4>Пользователей в базе: <b>{{ $allCount }}</b><br><br>


<form action="{{route('showusers')}}" method="post">
@csrf

<label class='label margin'><b>Сколько человек отобразить из базы?</b></label><br>
<input type="text" name="count"  class="textbox label" placeholder=""  value="<?php if(isset($_POST['count'])) echo $_POST['count']; else echo '10';?>" aria-describedby="button-addon2" /> </label>
<input type="hidden" name="id" value="{{$id}}" />
<input type="hidden" name="name" value="{{$name}}" />
<input type="submit" class="btn btn-success btn_size"  value="Получить пользователей">
</form>
<br>

<table class='table'>
<thead>
	<tr>
        <th class='ShowGroupName' width="1%">#</th>
        <th colspan='2' class='ShowGroupName'>Профиль</th>
        <th class='ShowGroupName'>Действие</th>
	</tr>
</thead>
<tbody>
@if(isset($data))
    @php $i = 0; @endphp
   @foreach($data as $value)
	<tr>
        <td width="1%">{{ ++$i }}</td>
        <td width="1%"><img src="{{$value['photo_50']}}" class='circle' width='40' height='40'></td>
		<td>{{$value['first_name'].' '.$value['last_name']}}</td>
		<td>
            <a href=https://vk.ru/id{{$value['id']}} target=_blank class='btn btn-outline-success btn-sm' id='btn-views'>Открыть профиль</a>
            <a href=https://vk.ru/write{{$value['id']}} target=_blank class='btn btn-outline-success btn-sm' id='btn-views'>Написать в ЛС</a>
        </td>
    </tr>
	@endforeach


</tbody>
</table>


<form method="post" action="{{ route('showusersdelete') }}">
    @csrf

    <input type="hidden" name="count" value="{{$count}}">
    <input type="hidden" name="id" value="{{$id}}">

    <input type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')" value="Удалить этих пользователей из базы">
</form>
@endif


    </div>
</div>

@endsection
