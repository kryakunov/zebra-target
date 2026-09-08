@extends('layout')

@section('content')
<div class="tab">
@if(session('token'))
<h1 class="title">Сбор обсуждений в группе</h1>
@endif
<div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')

@include('errors.exceptions')
@include('errors.session')

<form action="{{route('gettopics')}}" method="post" class="mb-4">
@csrf

@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте список сообществ:</label> 
<div class="form-group mb-3">       
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror"  name="groups" rows="5" placeholder="Вставьте группы по одной ссылке на строку">{{isset($request['groups']) ? $request['groups'] : null }}</textarea>
    @error('groups') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif


<label class='label margin'><b>Количество активностей:</b></label><br>
<div class='row'>	
	<div class='col-md-3'>
	<input type="text" size="20" name="ot" class="textbox" placeholder="от"  value="{{isset($request['ot']) ? $request['ot'] : null }}" aria-describedby="button-addon2" /> 
</div><div class='col-md-3'>
	<input type="text" size="20" name="do" class="textbox" placeholder="до (включительно)"  value="{{isset($request['do']) ? $request['do'] : null }}" aria-describedby="button-addon2" /> 
</div></div>
<small><label class='label'>*2 комментария = 2 активности. 2 комментария и 1 лайк = 3 активности.</label></small>

<br><br>
<label class='label margin'><b>Учитывать только те посты, которые были опубликованы в период:</b></label>
<div class='row'>
	<div class='col-md-3'>
<input type="date" size="3" id="time_min"  class="form-control" name="time_min" value="{{isset($request['time_min']) ? $request['time_min'] : null }}">
	</div>
	<div class='col-md-3'>
<input type="date" size="3"  id="time_max"  class="form-control" name="time_max" value="{{isset($request['time_max']) ? $request['time_max'] : null }}">
	</div>
</div> 


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

<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day.'\',\''.$time_max; ?>')">день</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day3.'\',\''.$time_max; ?>')">три дня</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $week.'\',\''.$time_max; ?>')">неделя</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $month.'\',\''.$time_max; ?>')">месяц</a>, 
<a style="cursor: pointer; color: #AE0000;" onclick="datachange('')">сбросить</a>
<br><br>

<label class='label'><b>Придумайте название задачи:</b></label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Активность в группе"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>

	<input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Создать задачу "  onclick="change()">
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



@include('partials.tool-guest-end')
@endsection