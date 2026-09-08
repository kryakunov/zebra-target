@extends('layout')

@section('content')
<div class="tab">
@if(session('token'))
<h1 class="title">Повторяющиеся N раз</h1>
@endif
<div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')


<form action="{{route('tool5Post')}}" method="post" class="mb-4">
@csrf

<label class='label'>Вставьте список элементов:</label> 
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="7" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users']) ? $request['users'] : null }}</textarea>
    @error('users') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

Искать только повторяющиеся 
от <input type="text" size="3" name="ot"  class="textbox label" placeholder=""  value="{{isset($request['ot']) ? $request['ot'] : '2' }}" aria-describedby="button-addon2" /> 
до <input type="text" size="3" name="do"  class="textbox label" placeholder=""  value="{{isset($request['do']) ? $request['do'] : '5' }}" aria-describedby="button-addon2" /> 
раз значения</label><br><br>
			

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск"  onclick="change()">
</form> 


@include('errors.exceptions')
@include('errors.session')



@if(isset($data))
    <div class="alert alert-success">
        В финальном списке: <b>{{count($data)}}</b>  элементов
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif



@include('partials.tool-guest-end')
@endsection
