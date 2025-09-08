@extends('layout')

@section('title') @parent Фильтр сообществ @endsection

@section('content')
<div class="tab">
<div class="title">Фильтр сообществ</div>
<div class="content">

@include('_ScriptDesk')


@include('errors.exceptions')
@include('errors.session')



<form action="{{route('filtergroups')}}" method="post" class="mb-4">
@csrf


@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте список сообществ:</label>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror"  name="groups" rows="5" placeholder="Вставьте группы по одной ссылке на строку">{{isset($request['groups']) ? $request['groups'] : null }}{{old('groups')}}</textarea>
    @error('groups')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@endif

<div class="result_format">
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="can_post" value="1" {{ isset($request['can_post']) ? 'checked' : '' }}></input> Только сообщества с открытой стеной</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="market" value="1" {{ isset($request['market']) ? 'checked' : '' }}></input> Только сообщества с товарами</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="verified" value="1" {{ isset($request['verified']) ? 'checked' : '' }}</input> Только верифицированные сообщества</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="trending" value="1" {{ isset($request['trending']) ? 'checked' : '' }}</input> Только сообщества у которых есть «огонёк».</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="can_message" value="1" {{ isset($request['can_post']) ? 'can_message' : '' }}</input> Только сообщества с открытыми личными сообщениями</label><Br>
</div>



<div class='row'>
	<div class='col-md-3'>
		<label class='label'>Участников от </label><br><input type="text" size="15" name="ot" class="textbox" placeholder="От"  value="{{ isset($request['ot']) ? $request['ot'] : old('ot') }}" aria-describedby="button-addon2" />
	</div>
	<div class='col-md-3'>
		<label class='label'>Участников до </label><br><input type="text" size="15" name="do" class="textbox" placeholder="До"  value="{{ isset($request['do']) ? $request['do'] : old('do') }}" aria-describedby="button-addon2" />
	</div>
</div>

<br>

<div class='row'>
	<div class='col-md-6'>

		<label class='label'>Исключить сообщества, <br>в названии которых есть слова:</label>
		<div class="form-group">
			<textarea class="output-panel form-control"  name="stop_words" rows="3" placeholder="По одному ключу на строку"><?php
				if (isset($_POST['stop_words'])) echo $_POST['stop_words']; ?></textarea>
		</div>
<br>
		<label class='label'>Закрытые группы:</label>
		<select name="closed"  class="form-control" id="dark">
		<?php if(!isset($_POST['closed'])) $_POST['closed'] = '0'; ?>
			<option value=0   <?php if ($_POST['closed'] == '0') echo 'selected'; ?>>Включить в поиск</option>
			<option value=1   <?php if ($_POST['closed'] == '1') echo 'selected'; ?>>Исключить из поиска</option>
			<option value=2   <?php if ($_POST['closed'] == '2') echo 'selected'; ?>>Найти только закрытые группы</option>
		</select>
<br>

	<label class='label'>Стена:</label>
	<select name="wall" class="form-control" id="dark">
	<?php if(!isset($_POST['wall'])) $_POST['wall'] = 9;  ?>
		<option value=9 <?php echo ($_POST['wall'] == 9) ? 'selected' : ''; ?> >Всё равно</option>
  		<option value=0 <?php echo ($_POST['wall'] == 0) ? 'selected' : ''; ?> >Выключена</option>
  		<option value=1 <?php echo ($_POST['wall'] == 1) ? 'selected' : ''; ?> >Открытая</option>
  		<option value=2 <?php echo ($_POST['wall'] == 2) ? 'selected' : ''; ?> >Ограниченная</option>
  		<option value=3 <?php echo ($_POST['wall'] == 3) ? 'selected' : ''; ?> >Закрытая</option>
  	</select>
<br>

<?php
	$time_max = date('Y-m-d');

	$date = new DateTime('-3 days');
	$day3 = $date->format('Y-m-d');

	$date = new DateTime('-7 days');
	$week = $date->format('Y-m-d');

	$date = new DateTime('-1 month');
	$month = $date->format('Y-m-d');

	$date = new DateTime('-3 months');
	$month3 = $date->format('Y-m-d');
?>

<label class='label'>Фильтр по активности. <br>Последний пост был не позднее, чем:</label><br>
<input type="date" class="form-control" id="time_min" name="date" <?php if (isset($_POST['date'])) echo "value='".$_POST['date']."'"; ?> >
</div>
</div>
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day3.'\',\''.$time_max; ?>')">три дня назад</a>,
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $week.'\',\''.$time_max; ?>')">неделю назад</a>,
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $month.'\',\''.$time_max; ?>')">месяц назад</a>,
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $month3.'\',\''.$time_max; ?>')">три месяца назад</a>,
<a style="cursor: pointer; color: #AE0000;" onclick="datachange('')">сбросить</a>
<br><br>



<label class='label'>Тип сообщества:</label>
<div class="result_format">
	<?php if(!isset($_POST['type'])) $_POST['type'] = 'all'; ?>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'all') echo 'checked'; ?> class=radio name=type type=radio value=all> Любой</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'group') echo 'checked'; ?> class=radio  name=type type=radio value=group> Группа</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'page') echo 'checked'; ?> class=radio  name=type type=radio value=page> Страница</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'event') echo 'checked'; ?> class=radio name=type type=radio value=event> Событие</label><br>
</div>
<br>


<label class='label'><b>Придумайте название задачи:</b></label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Фильтр сообществ"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>

	<input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Начать фильтрацию"  onclick="change()">
</form>
<br>

@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
https://vk.ru/club{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif



@endsection
