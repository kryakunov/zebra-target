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
    <h4>Черный список</h4>
    В этот список попадают все пользователи, которых вы удалили при работе с профилями из облака. <br><br>
    Значений в списке: {{ count($users) }}<br>
        <div class="form-group mb-3"> 
            <textarea class="output-panel form-control @error('data') is-invalid @enderror mb-2" id="textarea" name="data" rows="12" >@foreach($users as $user){{$user."\n"}}@endforeach</textarea>
            <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button>  
            <br><br>
        </div>
    </div>

</div> 

@endsection