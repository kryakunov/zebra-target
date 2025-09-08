@extends('layout')

@section('title') @parent Создать шаблон @endsection

@section('content')
<div class="tab">
<div class="title">Создать шаблон</div>
<div class="content">

<?php


$sidebar1 = [

    ['name' => 'Работа с сообществами', 'class' => 'hr'],
    ['name' => 'Поиск сообществ', 'uri' => 'searchgroups', 'img' => 'icon_search2.png'],
    ['name' => 'Фильтр сообществ', 'uri' => 'filtergroups', 'img' => 'filter.png'],
   // ['name' => 'Сбор участников сообществ', 'uri' => 'getallmembers', 'img' => 'icon_group.png'],
    ['name' => 'Сбор участников сообществ', 'uri' => 'getallmembers', 'img' => 'icon_group.png'],
    ['name' => 'Сбор активности в группе', 'uri' => 'getactivitygroups', 'img' => 'fire.png'],
    ['name' => 'Сбор администраторов групп', 'uri' => 'getgroupcontacts', 'img' => 'admins.png'],
   // ['name' => 'Доп. фильтр пользователей', 'uri' => 'extfilter', 'img' => 'filter.png'],
];
$sidebar2 = [
    ['name' => 'Работа с пользователями', 'class' => 'hr'],
    ['name' => 'Фильтр пользователей', 'uri' => 'usersfilter', 'img' => 'filter.png'],
   // ['name' => 'Анализ аудитории сообщества', 'uri' => 'analiz', 'img' => 'icon_piechart.png'],
    ['name' => 'Активность на странице', 'uri' => 'getactivityuser', 'img' => 'icon_heart_alt.png'],
    ['name' => 'Сбор друзей и подписчиков', 'uri' => 'getfriends', 'img' => 'peoples.png'],
    ['name' => 'Сбор пар и родственников', 'uri' => 'getrelatives', 'img' => 'icon_profile.png'],
    ['name' => 'Группы, где сидит ЦА', 'uri' => 'usersgroups', 'img' => 'icon_ol.png'],
    ['name' => 'Все сообщества пользователей', 'uri' => 'usersallgroups', 'img' => 'icon_ol.png'],
    ['name' => 'Лидеры мнений', 'uri' => 'opinionliders', 'img' => 'icon_lightbulb.png'],
   // ['name' => 'Топ читатели', 'uri' => 'topfollowers', 'img' => 'icon_profile.png'],
    ['name' => 'Сбор соц. сетей', 'uri' => 'socialnetworks', 'img' => 'social_instagram_circle.png'],
];
$sidebar3 = [
    ['name' => 'Работа с постами', 'class' => 'hr'],
    ['name' => 'Поиск постов по всему ВК', 'uri' => 'getposts', 'img' => 'icon_search.png'],
    ['name' => 'Поиск промо-постов', 'uri' => 'getpromoposts', 'img' => 'icon_search.png'],
    ['name' => 'Сбор активности в постах', 'uri' => 'getactivityposts', 'img' => 'icon_heart_alt.png'],


];
?>

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')


<a class="btn btn-outline-dark btn-sm" href="{{ route('mysamples') }}"><< Назад</a>
<br><br>
 
<h6>Выберите задачу для первого шага:</h6>

<div class="row">
<div class="col-md-4">
    
    <ul class="nav flex-column">
    @php $i = 0; @endphp

    @foreach ($sidebar1 as $item)
        @if(isset($item['class']))

            @php   $i++; @endphp

            <span class='hr'>{{$item['name']}}</span>

            @php continue; @endphp

        @endif
        <li class="menu-element-{{$i}}">
        <a class="list-link-new" href="{{ route('createsample', $item['uri']) }}">
            <img src="https://zebra-target.ru/PNG/{{$item['img']}}" width="16" height="16"> {{$item['name']}}</a>
        </li>
    @endforeach
    </ul>


</div>
<div class="col-md-4">

<ul class="nav flex-column">
    @php $i = 0; @endphp

    @foreach ($sidebar2 as $item)
        @if(isset($item['class']))

            @php   $i++; @endphp

            <span class='hr'>{{$item['name']}}</span>

            @php continue; @endphp

        @endif
        <li class="menu-element-{{$i}}">
        <a class="list-link-new" href="{{ route('createsample', $item['uri']) }}">
            <img src="https://zebra-target.ru/PNG/{{$item['img']}}" width="16" height="16"> {{$item['name']}}</a>
        </li>
    @endforeach
    </ul>


</div>
<div class="col-md-4">
<ul class="nav flex-column">
    @php $i = 0; @endphp

    @foreach ($sidebar3 as $item)
        @if(isset($item['class']))

            @php   $i++; @endphp

            <span class='hr'>{{$item['name']}}</span>

            @php continue; @endphp

        @endif
        <li class="menu-element-{{$i}}">
            <a class="list-link-new" href="{{ route('createsample', $item['uri']) }}">
            <img src="https://zebra-target.ru/PNG/{{$item['img']}}" width="16" height="16"> {{$item['name']}}</a>
        </li>
    @endforeach
    </ul>

</div>
</div>
</div>



@endsection