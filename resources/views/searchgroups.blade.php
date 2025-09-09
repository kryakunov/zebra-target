@extends('layout')

@section('title') @parent Поиск сообществ @endsection

@section('content')
<div class="tab">
<div class="title">Поиск сообществ</div>
<div class="content">
@include('_ScriptDesk')




<form action="{{route('searchgroups')}}" method="post" class="mb-4">
@csrf


<input type="hidden" name="parentId" value="{{ (isset($request['parent_id'])) ? $request['parent_id'] : '' }}">
<label class='label'>Ключевые фразы в названии сообщества:</label>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('q') is-invalid @enderror"  name="q" rows="5" placeholder="По одному ключу на строку">{{isset($request['q']) ? $request['q'] : null }}</textarea>
    @error('q')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


<div class="result_format">
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="strong" value="1" <?php  echo isset($_POST['strong']) ? 'checked' : ''; ?>></input> Точное вхождение ключевой фразы</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="market" value="1" <?php  echo isset($_POST['market']) ? 'checked' : ''; ?>></input> Только сообщества с товарами</label><Br>
</div>

<div class='row'>
	<div class='col-md-6'>

		<label class='label'>Минус-слова:</label>
		<div class="form-group">
			<textarea class="output-panel form-control"  name="stop_words" rows="3" placeholder="По одному ключу на строку"><?php
				if (isset($_POST['stop_words'])) echo $_POST['stop_words']; ?></textarea>
		</div>

		<label class='label'>Закрытые группы:</label>
		<select name="closed"  class="form-control" id="dark">
		<?php if(!isset($_POST['closed'])) $_POST['closed'] = '0'; ?>
			<option value=0   <?php if ($_POST['closed'] == '0') echo 'selected'; ?>>Включить в поиск</option>
			<option value=1   <?php if ($_POST['closed'] == '1') echo 'selected'; ?>>Исключить из поиска</option>
			<option value=2   <?php if ($_POST['closed'] == '2') echo 'selected'; ?>>Найти только закрытые группы</option>
		</select>
<br>
		<label class='label'>Сортировать:</label>
		<select name="sort" class="form-control" id="dark">
		<?php if(!isset($_POST['sort'])) $_POST['sort'] = '0'; ?>
			<option value="0" <?php if ($_POST['sort'] == '0') echo 'selected'; ?>>По умолчанию (как в ВК)</option>
			<option value="6" <?php if ($_POST['sort'] == '5') echo 'selected'; ?>>Cортировать по количеству пользователей</option>

		</select>
	</div>
</div>
<br>

<label class='label'>Тип сообщества:</label>
<div class="result_format">
	<?php if(!isset($_POST['type'])) $_POST['type'] = 'all'; ?>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'all') echo 'checked'; ?> class=radio name=type type=radio value=all> Любой</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'group') echo 'checked'; ?> class=radio  name=type type=radio value=group> Группа</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'page') echo 'checked'; ?> class=radio  name=type type=radio value=page> Страница</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'event') echo 'checked'; ?> class=radio name=type type=radio value=event> Событие</label><br>
	<label class="cursor-pointer"><input <?php if ($_POST['type'] == 'fevent') echo 'checked'; ?> class=radio  name=type type=radio value=fevent> Предстоящее событие</label><br>
</div>

<br>
<label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />

 <br> <br>
<br>
<input class="btn btn-success btn_size"  type="submit" id="btnMenu" value=" Начать поиск сообществ"  onclick="change()">


</form>
<br>


@include('errors.exceptions')
@include('errors.session')



@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
https://vk.com/club{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif



@endsection
