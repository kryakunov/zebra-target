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
        <div class="form-group mb-3"> 

        <form action="{{route('cloud.update', $id)}}" method="post" class="mb-4">
        @csrf
        @method('put')

            <label class='label margin'><b>Название списка:</b></label><br>
            <input type="text" size="50" name="name" class="textbox" value="{{ $name }}" aria-describedby="button-addon2" /> 
            <br><br>
            <textarea class="output-panel form-control @error('data') is-invalid @enderror mb-2" id="textarea" name="data" rows="12" >@foreach($data as $item){{$item."\n"}}@endforeach</textarea>
            <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Сохранить "  onclick="return confirm('Вы уверены?')">        <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button>  
        
        </form> 

        </div>
    </div>
</div> 

@endsection