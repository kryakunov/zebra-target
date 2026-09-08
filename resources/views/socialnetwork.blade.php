@extends('layout')

@section('content')
    <div class="tab">
    @if(session('token'))
<h1 class="title">Сбор социальных сетей</h1>
@endif
    <div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')

@include('errors.session')
@include('errors.exceptions')


<form class="parser" action="{{route('socialnetworks')}}" method="post">
@csrf

@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте ID пользователей по одному на строку:</label>
  <div class="form-group">       
    	<textarea class="output-panel form-control @error('users') is-invalid @enderror" name="users" rows="7" placeholder="По одному id на строку">{{isset($request['users']) ? $request['users'] : null }}</textarea>
</div>
    @error('users') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
@endif

<label class='label'><b>Что собираем?</b></label>
<div class="result_format">
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="instagram" value="1" {{ isset($request['instagram']) ? 'checked' : '' }}></input> Instagram</label><Br>
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="facebook" value="1" {{ isset($request['facebook']) ? 'checked' : '' }}></input> Facebook</label><Br>
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="skype" value="1" {{ isset($request['skype']) ? 'checked' : '' }}></input> Skype</label><Br>
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="twitter" value="1" {{ isset($request['twitter']) ? 'checked' : '' }} ></input> Twitter</label><Br>
</div>

<label class='label'><b>Придумайте название задачи:</b></label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Сбор участников сообществ"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br><br>


<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Создать задачу "  onclick="change()">
</form> <br>



@include('errors.exceptions')
@include('errors.session')



@if(isset($data['instagram']))
@php $done = true @endphp
<div class="alert alert-success">
        Найдено instagram-аккаунтов: <b>{{count($data['instagram'])}}</b>
    </div>
<textarea class="output-panel form-control mb-2"  id="textarea" rows="7">@foreach($data['instagram'] as $id)
{{$id}}
@endforeach</textarea><br>
@endif

@if(isset($data['skype']))
@php $done = true @endphp
    <div class="alert alert-success">
        Найдено skype-аккаунтов: <b>{{count($data['skype'])}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="7">@foreach($data['skype'] as $id)
{{$id}}
@endforeach</textarea><br>
@endif


@if(isset($data['facebook']))
@php $done = true @endphp
    <div class="alert alert-success">
        Найдено facebook-аккаунтов: <b>{{count($data['facebook'])}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="7">@foreach($data['facebook'] as $id)
{{$id}}
@endforeach</textarea><br>
@endif


@if(isset($data['twitter']))
@php $done = true @endphp
    <div class="alert alert-success">
        Найдено twitter-аккаунтов: <b>{{count($data['twitter'])}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="7">@foreach($data['twitter'] as $id)
{{$id}}
@endforeach</textarea>
@endif

@if(!isset($done) and isset($data))
    <div class="alert alert-danger">
        Не найдено
    </div>
@endif

@include('partials.tool-guest-end')
@endsection




