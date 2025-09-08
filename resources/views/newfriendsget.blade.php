<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@section('title') Zebra Target @show</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css" >
    <script type="text/javascript" src="/scripts.js"></script>
    <meta property="og:image" content="https://zebra-target.ru/logo-small.png">
    <meta property="og:title" content="Зебра Таргет - инструмент для поиска целевой аудитории">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://vk.ru/js/api/openapi.js?169" type="text/javascript"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
<div class="container text-center">
    <div class="row">

<div class="tab">
<div class="title">Отслеживание новых друзей у пользователей</div>
<div class="content">
<h6>Как работает этот скрипт?</h6>
<p class="ScriptDesc">
Дообавьте группы в отслеживание и находите новых подписчиков <a href="#">Видео-туториал</a>
</p>


        <a class="btn btn-outline-success btn-sm" href="{{route('NewFriendsShow')}}">Назад</a>
        <br><br>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@forelse($data as $id){{$id}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>

<button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button>
    </div>
</div>
</div>
</div>

</body>

