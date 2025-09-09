@extends('layout')

@section('title') @parent Сбор участников сообществ @endsection

@section('content')
<div class="tab">
<div class="title">Сбор участников сообщества</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.session')

<form action="{{route('getmembers')}}" method="post" class="mb-4">
@csrf

<input type="hidden" name="parentId" value="{{ (isset($request['parent_id'])) ? $request['parent_id'] : '' }}">

@if(isset($work))
	@include('_work')
@else


<label class='label'>Вставьте список сообществ:</label> <br>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror"  name="groups" rows="5" placeholder="Вставьте группы по одной ссылке на строку">{{ (isset($request['groups'])) ? $request['groups']: old('groups')  }}</textarea>
    @error('groups')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@endif


<label class='label'>Найти только тех, кто состоит минимум в

<input type="text" size="4" name="min" class="textbox" value="{{ (isset($request['min'])) ? $request['min']: old('min') }}" aria-describedby="button-addon2" />
сообществах из списка</label><br><br>


<label class='label'><b>Придумайте название задачи:</b></label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Сбор участников сообществ"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>

<!--
Результаты в формате:<br>
<div class="result_format mb-3">
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='id'> ID пользователей вида: 12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_id'> ID пользователей вида: id12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_com_id'> ID пользователей вида: vk.com/id12345</label>
</div>-->
<input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Начать поиск аудитории"  onclick="change()">
</form>



@if(isset($items))
    <div class="alert alert-success">
        Найдено: <b>{{count($items)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($items as $key => $value)
{{$value}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif

</div></div>
@endsection
