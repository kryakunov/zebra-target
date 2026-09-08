@extends('layout')

@section('title') @parent Сбор участников сообществ @endsection

@section('content')
<div class="tab">
<div class="title">Сбор участников сообществ</div>
<div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')

@include('errors.exceptions')
@include('errors.session')

<form action="{{route('getallmembers')}}" method="post" class="mb-4">
@csrf

@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте список сообществ:</label> <br>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('groups') is-invalid @enderror"  name="groups" rows="6" placeholder="По одному ID на строку">{{ (isset($request['groups'])) ? $request['groups']: old('groups')  }}</textarea>
    @error('groups')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@endif

<label class='label'><b>Придумайте название задачи:</b></label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Сбор участников сообществ"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>

<!--
Результаты в формате:<br>
<div class="result_format mb-3">
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='id'> ID пользователей вида: 12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_id'> ID пользователей вида: id12345</label><br>
    <label id='pointer' style="cursor: pointer"><input  style="cursor: pointer" id=radio name=result_format type=radio value='vk_com_id'> ID пользователей вида: vk.ru/id12345</label>
</div>-->
<input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Создать задачу "  onclick="change()">
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
@include('partials.tool-guest-end')
@endsection
