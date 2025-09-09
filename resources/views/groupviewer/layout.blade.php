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
    <!--<script type="text/javascript" src="https://vk.com/js/api/openapi.js?162"></script>-->
</head>
  <body>
  <div class="container text-center">
  <div class="row">





  <div class="navbar_right">
        @if(session('token'))
        <div class="dropdown">
          <img src="{{session('photo')}}" class="circle" width="45" height="45">

            @if(session('access') > '4070900800')
            <a class="zoloto dropdown-toggle" href="#"  data-bs-toggle="dropdown" aria-expanded="false">
              <span class='eternal_access'>Вечный доступ</span>
            </a>
            @else
            <a class="btn btn-secondary dropdown-toggle noborder " href="#" id="access" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              @if(session('access') > time())
                Полный доступ до <small>{{date("d.m.Y", session('access'))}}</small>
              @else
                Бесплатный доступ
              @endif

            @endif

          </a>
          <ul class="dropdown-menu">
            <li class="drop-menu"><a class="dropdown-item" href="/price">Цены и тарифы</a></li>
            <li class="drop-menu"><a class="dropdown-item" href="/profile">Менеджер задач</a></li>
            <li class="drop-menu"><a class="dropdown-item" href="/partner">Партнерская программа</a></li>
            <li><hr class="dropdown-divider"></li>
            <li class="drop-menu"><a class="dropdown-item" href="/logout">Выйти</a></li>
          </ul>
        </div>
      @else
        <a href="{{route('vkLogin')}}" class="btn btn-primary vk-button">Войти через ВК</a>
      @endif
  </div>

@yield('content')

</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(61877542, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>

  </body>
</html>
