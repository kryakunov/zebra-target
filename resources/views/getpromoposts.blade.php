@extends('layout')

@section('title') @parent Сбор промо-постов @endsection

@section('content')
<div class="tab">
<div class="title">Сбор промо-постов</div>
<div class="content">
@include('_ScriptDesk')

<form action="{{route('getpromoposts')}}" method="post" class="mb-4">
@csrf


@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте группы:</label>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror"  name="groups" rows="5" placeholder="По одной группе на строку">{{isset($request['groups']) ? $request['groups'] : null }}</textarea>
    @error('groups')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif


<br>
    <label class='label'>Минимум лайков:</label><br>
    <input type="text" size="4" name="likes_min"  class="textbox" value="{{isset($request['likes_min']) ? $request['likes_min'] : old('likes_min') }}" value=""/>
<br><br>

<label class='label margin'>Искать посты начиная с даты:</label><br>

<input type="date" size="4" id="time_min"  class="textbox" name="time_min" value="{{isset($request['time_min']) ? $request['time_min'] : '' }}">
<br>



<?php
	$time_max = date('Y-m-d');
	$date = new DateTime('-1 days');
	$day = $date->format('Y-m-d');

	$date = new DateTime('-3 days');
	$day3 = $date->format('Y-m-d');

	$date = new DateTime('-7 days');
	$week = $date->format('Y-m-d');

	$date = new DateTime('-1 month');
	$month = $date->format('Y-m-d');
?>

<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day3.'\',\''.$time_max; ?>')">за три дня</a>,
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $week.'\',\''.$time_max; ?>')">за неделю</a>,
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $month.'\',\''.$time_max; ?>')">за месяц</a>,
<a style="cursor: pointer; color: #AE0000;" onclick="datachange('')">сбросить</a><br>
<small><label class='label'>* Если не указано, то берутся посты за последний месяц</label></small>
<br><br>
<!--
Результаты в формате:<br>
<div class="result_format mb-3">
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='id'> ID пользователей вида: 12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_id'> ID пользователей вида: id12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_com_id'> ID пользователей вида: vk.ru/id12345</label>
</div>-->

<label class='label'>Придумайте название задачи:</label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder=""  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>


<input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Начать поиск "  onclick="change()">
</form>

@include('errors.exceptions')
@include('errors.session')

@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $value)<?php
if($_POST['when_posts'] !== "3") echo "https://vk.ru/wall".$value."\n";
else echo $value."\n";
?>
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif

</div></div>
@endsection
