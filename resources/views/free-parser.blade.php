@extends('layout')

@section('title') Бесплатный парсер вконтакте @endsection

@section('content')
<div class="tab">
<div class="title">Бесплатный парсер вконтакте</div>
<div class="content">

@include('errors.exceptions')
@include('errors.session')
@include('errors.validate')


<h1>Бесплатный парсер вконтакте</h1>

<hr>
Добро пожаловать на наш сервис! С помощью него вы можете бесплатно парсить целевую аудиторию из ВК на бесплатном доступе. Для этого достаточно войти через вк. <br><br>
Будем рады, если вы по достоинству оцените функционал сервиса. Мы уверены, здесь вы сможете найти парсер на любой вкус.
Если у вас будут вопросы по сервису или предложения, можете написать нам в <a href="https://t.me/zebratarget" target="_blank"><b>телеграм-канал</b></a>
<br><br>Много полезных материалов о сервисе вы можете найти в нашей <a href="http://vk.com/zebratarget_ru" target="_blank"><b>группе ВК</b></a>, присоединяйтесь!
<br><br>А также, можете посмотреть <a href="https://youtu.be/k3FgcbYv7qg" target="_blank"><b>Видео-обзор сервиса</b></a>



@endsection
