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
    <script src="https://vk.com/js/api/openapi.js?169" type="text/javascript"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
<div class="container text-center">
    <div class="row">
<div class="tab">
<div class="content">



<br><br>
        <a class="btn btn-outline-success btn-sm mt-10 mb-10" href="{{route('NewFriendsShow')}}">Назад</a>


    @forelse($data as $user)
        <div style="display: flex; margin-top: 10px; align-items: center;">
            <div>
            <img src="{{$user['photo']}}" width="50" class="circle"></div>
            <div style="margin-left: 10px;"><a href="http://vk.com/id{{$user['id']}}"  class='ShowGroupName' target="_blank">{{$user['first_name']}} {{$user['last_name']}}</a></div>
        </div>
    @empty
        Новых друзей не найдено
    @endforelse



    </div>
</div>
</div>
</div>

