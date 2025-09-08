
@extends('layout')

@section('title') {{ $work->name }} - @parent @endsection

@section('content')
<div class="tab">
<div class="title">{{ $work->name }}</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

<a href="{{route('myworks')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a> 
<br><br>


@if(isset($data['instagram']))
<label class='label'>
        Найдено instagram-аккаунтов: <b>{{count($data['instagram'])}}</b>
</label>
<textarea class="output-panel form-control mb-2"  id="textarea" rows="7">@foreach($data['instagram'] as $id)
{{$id}}
@endforeach</textarea>   <button type="button" class="btn btn-outline-dark btn-sm"  onclick="copy()">Скопировать</button> <br>

<br>
@endif

@if(isset($data['skype']))
<label class='label'>
        Найдено skype-аккаунтов: <b>{{count($data['skype'])}}</b>
        </label>
        <textarea class="output-panel form-control mb-2"  id="textarea2" rows="7">@foreach($data['skype'] as $id)
{{$id}}
@endforeach</textarea><button type="button" class="btn btn-outline-dark btn-sm"  onclick="copy2()">Скопировать</button><br><br>
@endif


@if(isset($data['facebook']))
<label class='label'>
        Найдено facebook-аккаунтов: <b>{{count($data['facebook'])}}</b>
        </label>
        <textarea class="output-panel form-control mb-2"  id="textarea3" rows="7">@foreach($data['facebook'] as $id)
{{$id}}
@endforeach</textarea><button type="button" class="btn btn-outline-dark btn-sm"  onclick="copy3()">Скопировать</button> <br><br>
@endif


@if(isset($data['twitter']))
<label class='label'>
        Найдено twitter-аккаунтов: <b>{{count($data['twitter'])}}</b>
        </label>
        <textarea class="output-panel form-control mb-2"  id="textarea4" rows="7">@foreach($data['twitter'] as $id)
{{$id}}
@endforeach</textarea><button type="button" class="btn btn-outline-dark btn-sm"  onclick="copy4()">Скопировать</button> <br>
@endif



</div>
</div> 

@endsection
