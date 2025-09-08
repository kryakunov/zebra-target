@extends('layout')

@section('title') @parent Техническая поддержка @endsection

@section('content')
<div class="tab">
<div class="title">Вопрос №  {{$item->id}} - @if($item->status == false) На рассмотрении @else Закрыт @endif</div>
<div class="content">


<a class="btn btn-outline-success btn-sm" href="{{route('support')}}"><< Назад</a><br><br>

@include('errors.exceptions')
@include('errors.session')

<label class='label'><b>Тема: {{ $item->topic }}</b></label><br><br>

@foreach($item->questions as $question)
    <?php if($question->role == 0) $role = 'warning'; else $role = 'info'; ?>
    <div class="alert alert-{{$role}}" role="alert">
        {{ $question->text }}
    </div>
@endforeach


@if($item->status == false)
    <form action="{{route('questionStore', $item->id)}}" method="post" class="mb-4">
    @csrf

    <div class="form-group mb-3">    
        <label class='label'><b>Ответить:</b></label><br>
        <textarea class="output-panel form-control @error('question') is-invalid @enderror"  name="question" rows="6" placeholder="">{{isset($request['question']) ? $request['question'] : null }}</textarea>
        @error('question') 
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Отправить">
    </div>
@endif

@endsection