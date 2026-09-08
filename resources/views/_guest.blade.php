@extends('layout')

@section('robots') noindex, nofollow @endsection

@section('content')
<div class="tab">

<div class="title">Войдите, чтобы пользоваться сервисом</div>
<div class="content">
    <h1>Нужна авторизация</h1>
    <p>Этот раздел доступен после входа через ВКонтакте. Страницы инструментов можно смотреть без регистрации, но запуск парсинга, сохранение результатов и личные данные закрыты.</p>
    @include('errors.session')
    @include('partials.guest-cta')
</div>
</div>

@endsection
