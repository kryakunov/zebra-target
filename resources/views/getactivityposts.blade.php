@extends('layout')

@section('title') @parent Сбор активности в постах @endsection

@section('content')
<div class="tab">
<div class="title">Сбор активности в постах</div>
<div class="content">
@include('_ScriptDesk')


@include('errors.exceptions')
@include('errors.session')

<form action="{{route('getactivityposts')}}" method="post" class="mb-4">
@csrf
                
@if(isset($work))
    @include('_work')
@else
    <div class="form-group mb-3">       
        <textarea class="output-panel form-control @error('posts') is-invalid @enderror"  name="posts" rows="5" placeholder="По одной ссылке на строку">{{isset($request['posts']) ? $request['posts'] : null }}</textarea>
        @error('posts') 
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif


<label class='label'><b>Какие активности собираем?</b></label> 
<div class="result_format"> 
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="likes" value="1"  {{isset($request['likes']) ? 'checked' : null }}></input> Лайки</label><br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="comments" value="1"  {{isset($request['comments']) ? 'checked' : null }}></input> Комментарии</label><br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="thread_comments" value="1" {{isset($request['thread_comments']) ? 'checked' : null }}></input> Комментарии > комментарии</label><Br>
</div>

<label class='label'>Придумайте название задачи:</label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder=""  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br>


	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать сбор активности "  onclick="change()">
</form> 
<br>





@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif


@endsection