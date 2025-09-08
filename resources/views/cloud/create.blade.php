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

    <form action="{{route('cloud.store')}}" method="post" class="mb-4">
        @csrf


        <label class='label margin'>Название списка:</label><br>
        <input type="text" size="50" name="name" class="textbox"   value="{{ old('name') }}" aria-describedby="button-addon2" /> 

        <br><br>
        <label class='label'>Выберите тип</label><br>
        <div class="result_format">
            <label class="cursor-pointer"><input class="radio" type="radio"  name="type" value="12" checked>Пользователи</label><br>
            <label class="cursor-pointer"><input class="radio" type="radio"  name="type" value="13" <?php if (isset($request['type']) and ($request['type']== "13") ) { echo 'checked'; } ?>>Сообщества</label><br>
        </div> 

        <label class='label margin'>Вставьте базу (по одному ID на строку):</label><br>
        <div class="form-group mb-3">       
            <textarea class="output-panel form-control @error('data') is-invalid @enderror"  name="data" rows="8" >{{ old('data') }}</textarea>
            @error('data') 
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Добавить "  onclick="change()">
    </form> 

    </div>
</div> 

@endsection