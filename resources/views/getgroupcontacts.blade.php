@extends('layout')

@section('title') @parent Сбор контактов групп @endsection

@section('content')
<div class="tab">
<div class="title">Сбор людей из поля "контакты" в сообществах</div>
<div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')


<form action="{{route('getgroupcontacts')}}" method="post" class="mb-4">
@csrf

@if(isset($work))
	@include('_work')
@else

<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror" name="groups" rows="5" placeholder="Вставьте группы по одной ссылке на строку">{{isset($request['groups']) ? $request['groups'] : null }}</textarea>
    @error('groups')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif

<label class='label'><b>Придумайте название задачи:</b></label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Сбор администраторов групп"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>


<!--
Результаты в формате:<br>
<div class="result_format mb-3">
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='id'> ID пользователей вида: 12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_id'> ID пользователей вида: id12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_com_id'> ID пользователей вида: vk.ru/id12345</label>
</div>-->
<input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Начать поиск аудитории"  onclick="change()">
</form>

@include('errors.session')
@include('errors.exceptions')

@if(isset($items))
    <div class="alert alert-success">
        Найдено: <b>{{count($items)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="9">@foreach($items as $key => $value)
<?php if(isset($value['user_id'])) echo $value['user_id']."\n"; ?>
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif

</div></div>
@include('partials.tool-guest-end')
@endsection
