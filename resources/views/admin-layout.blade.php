<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Зебра Таргет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css" >
    <script type="text/javascript" src="scripts.js"></script>
</head>
  <body>
  <div class="container text-center">
  <div class="row">

    <div class="col-md-3">
  <div class="navbar">
    <a href="/"><img src="/logo2.png" width="220"></a>
  </div>

  <div class="profile-left">
    
      <a href="/"><img src="https://zebra-target.ru/PNG/icon_house.png" width="16" height="16"> На главную</a>
    </div>

    <div class="profile-left">
      <a href="/admin">Краткая сводка</a>
    </div>

    <div class="profile-left">
      <a href="{{route('admin2')}}">Ответить на вопросы</a>
    </div>

<div class="profile-left">
  <a href="{{route('admin3')}}">Последние задачи</a>
</div>

<div class="profile-left">
  <a href="{{route('getsampleworks')}}">Последние задачи из шаблонов</a>
</div>


    <div class="profile-left">
      <a href="{{route('getpaymentusers')}}">Кто купил сервис?</a>
    </div>

<div class="profile-left">
  <a href="{{route('updates')}}">Обновления</a>
</div>

    <div class="profile-left">
      <a href="{{route('stats')}}">Статистика $$$</a>
    </div>


    </div>

<div class="col-md-9">
  <div class="navbar_right">
        @if(session('token'))
        <div class="dropdown"> 
          <img src="{{session('photo')}}" class="circle" width="45" height="45">
          <a class="btn btn-secondary dropdown-toggle noborder" href="#" id="access" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            @if(session('access') > time())
              Полный доступ до {{date("d.m.Y", session('access'))}}
            @else
              Бесплатный доступ
            @endif
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Мой профиль</a></li>
            <li><a class="dropdown-item" href="#">Партнерская программа</a></li>
            <li><a class="dropdown-item" href="/logout">Выйти</a></li>
          </ul>
        </div>
      @else
        <a href="{{$browser_url}}" class="btn btn-primary vk-button">Войти через ВК</a>
      @endif
  </div>




    @yield('content')

</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
  </body>
</html>