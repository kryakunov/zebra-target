@extends('layout')

@section('content')
<div class="tab">

<div class="title">Поиск и сбор целевой аудитории во ВКонтакте.</div>
<div class="content">
    <div class="alert alert-warning">
        <b><a href="{{route('vkLogin')}}">Войдите через ВК</a></b>, чтобы начать пользоваться сервисом.
    </div>
</div>
</div>

@endsection