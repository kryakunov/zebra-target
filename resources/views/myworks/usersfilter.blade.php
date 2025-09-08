@extends('layout')

@section('title') @parent Мои задачи @endsection

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

<br>

<p>В исходном задании: <b>{{ $work->source_count }}</b></p>
<p>В итоговом списке: <b>{{ $work->count }}</b></p>
 
<div class="row">
    <div class="col-md-6">

<textarea class="output-panel form-control mb-2"  id="textarea2" rows="13">@forelse($data as $value){{$value}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
<button type="button" class="btn btn-outline-dark btn-sm"  onclick="copy2()">Скопировать</button> <br>
    </div>
<div class="col-md-6">
    <b>Условия расчета: </b> <br>
    @include('myworks.filterparams')

</div>
</div>


    </div>
</div> 

@endsection



