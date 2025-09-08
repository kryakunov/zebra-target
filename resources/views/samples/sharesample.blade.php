@extends('layout')

@section('title') @parent Поделиться шаблоном @endsection

@section('content')
<div class="tab">
<div class="title">Поделиться шаблоном</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

    <a class="btn btn-outline-success btn-sm" href="{{route('mysamples')}}"><< Назад</a>
    <br><br>
        <div class="form-group mb-3"> 

        <form action="{{route('sharesamplesave')}}" method="post" class="mb-4">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">

        <div class="form-check form-switch">
        <br><label class="form-check-label" for="flexSwitchCheckChecked">
        <input name="share" class="form-check-input myradio" type="checkbox" id="flexSwitchCheckChecked" {{ ( $share == 1) ? 'checked' : '' }}>
        Открыть шаблон для просмотра другим пользователям </label><br><br>

      </div>

            <label class='label margin'>Вставьте ID пользователей, кому доступен шаблон для просмотра:</label><br>
            <textarea class="output-panel form-control @error('data') is-invalid @enderror mb-2" id="textarea" name="data" rows="12" >@if(is_array($data)) @foreach($data as $item){{$item."\n"}}@endforeach @endif</textarea>
            <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Сохранить " >
        
        </form> 

        </div>
    </div>
</div> 

@endsection