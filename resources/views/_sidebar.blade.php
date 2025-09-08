<?php



$sidebar = [
    ['name' => 'Цены и тарифы', 'uri' => 'price', 'img' => 'icon_wallet.png'],
    ['name' => 'Предложения', 'uri' => 'support', 'img' => 'icon_question_alt2.png'],
    ['name' => 'Партнерская программа', 'uri' => 'partner', 'img' => 'icon_briefcase.png'],
    ['name' => 'Мои базы', 'uri' => 'cloud', 'img' => 'icon_cloud_alt.png'],
    ['name' => 'Менеджер задач', 'uri' => 'myworks', 'img' => 'icon_cogs.png'],

    ['name' => 'Работа с сообществами', 'class' => 'hr'],
    ['name' => 'Поиск сообществ', 'uri' => 'searchgroups', 'img' => 'icon_search2.png'],
    ['name' => 'Фильтр сообществ', 'uri' => 'filtergroups', 'img' => 'filter.png'],
   // ['name' => 'Сбор участников сообществ', 'uri' => 'getallmembers', 'img' => 'icon_group.png'],
    ['name' => 'Сбор участников сообществ', 'uri' => 'getallmembers', 'img' => 'icon_group.png'],
    ['name' => 'Мониторинг вступлений', 'uri' => 'newmembers', 'img' => 'icon_hourglass.png'],
    ['name' => 'Сбор активности в группе', 'uri' => 'getactivitygroups', 'img' => 'fire.png'],
    ['name' => 'Сбор администраторов групп', 'uri' => 'getgroupcontacts', 'img' => 'admins.png'],
   // ['name' => 'Доп. фильтр пользователей', 'uri' => 'extfilter', 'img' => 'filter.png'],

    ['name' => 'Работа с пользователями', 'class' => 'hr'],
    ['name' => 'Фильтр пользователей', 'uri' => 'usersfilter', 'img' => 'filter.png'],
    ['name' => 'Мониторинг новых друзей', 'uri' => 'newfriends', 'img' => 'icon_hourglass.png'],
   // ['name' => 'Анализ аудитории сообщества', 'uri' => 'analiz', 'img' => 'icon_piechart.png'],
    ['name' => 'Активность на странице', 'uri' => 'getactivityuser', 'img' => 'icon_heart_alt.png'],
    ['name' => 'Сбор друзей и подписчиков', 'uri' => 'getfriends', 'img' => 'peoples.png'],
    ['name' => 'Сбор пар и родственников', 'uri' => 'getrelatives', 'img' => 'icon_profile.png'],
    ['name' => 'Группы, где сидит ЦА', 'uri' => 'usersgroups', 'img' => 'icon_ol.png'],
    ['name' => 'Все сообщества пользователей', 'uri' => 'usersallgroups', 'img' => 'icon_ol.png'],
    ['name' => 'Лидеры мнений', 'uri' => 'opinionliders', 'img' => 'icon_lightbulb.png'],
   // ['name' => 'Топ читатели', 'uri' => 'topfollowers', 'img' => 'icon_profile.png'],
    ['name' => 'Сбор соц. сетей', 'uri' => 'socialnetworks', 'img' => 'social_instagram_circle.png'],

    ['name' => 'Работа с постами', 'class' => 'hr'],
    ['name' => 'Поиск постов по всему ВК', 'uri' => 'getposts', 'img' => 'icon_search.png'],
    ['name' => 'Поиск промо-постов', 'uri' => 'getpromoposts', 'img' => 'icon_search.png'],
    ['name' => 'Сбор активности в постах', 'uri' => 'getactivityposts', 'img' => 'icon_heart_alt.png'],

    ['name' => 'Инструменты', 'class' => 'hr'],
    ['name' => 'Преобразовать ID в профили', 'uri' => 'tool1', 'img' => 'icon_id-2.png'],
    ['name' => 'Преобразовать ID в сообщества', 'uri' => 'showgroups', 'img' => 'icon_id-2.png'],
    ['name' => 'Поиск общих элементов', 'uri' => 'tool2', 'img' => 'icon_search.png'],
    ['name' => 'Вычесть элементы из списка', 'uri' => 'tool3', 'img' => 'icon_documents_alt.png'],
    ['name' => 'Удалить дубли', 'uri' => 'tool4', 'img' => 'icon_trash_alt.png'],
    ['name' => 'Повторяющиеся N раз', 'uri' => 'tool5', 'img' => 'icon_puzzle.png'],

];


if(session('access') > '4070900800') {
  unset($sidebar[0]);
}

$admins = ['52090716', '573204714', '185466160', '18277740', '52090716'];
if (in_array(session('id'), $admins)) array_unshift($sidebar,
['name' => 'Админ-панель', 'uri' => 'admin', 'img' => 'icon_cog.png'],
['name' => 'Мои шаблоны', 'uri' => 'mysamples', 'img' => 'icon_cog.png']);

?>

<div class="for-mobile">
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div style="margin-left: 10px">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="/"><span style="margin-left: 10px">Zebra Target</span></a>

        <div class="offcanvas offcanvas-end " tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel"><a class="navbar-brand" style="color: black" href="/"><span style="margin-left: 10px">Zebra Target</span></a></h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">


                    <ul class="nav flex-column">
                        @php $i = 0; @endphp

                        @foreach ($sidebar as $item)
                            @if(isset($item['class']))

                                @php   $i++; @endphp

                                <a class='hr' id='menu-name-{{ $i }}'>{{$item['name']}}</a>

                                @php continue; @endphp

                            @endif
                            <li class="nav-item">
                                <a class="nav-link list-link{{ Route::current()->uri() == $item['uri'] ? ' visited' : '' }}" href="/{{$item['uri']}}">
                                    <img src="https://zebra-target.ru/PNG/{{$item['img']}}" width="16" height="16"> {{$item['name']}}</a>
                            </li>
                        @endforeach
                    </ul>
                </ul>

            </div>
        </div>
    </div>
</nav>
</div>


<div class="for-desktop">
<ul class="nav flex-column">
@php $i = 0; @endphp

@foreach ($sidebar as $item)
    @if(isset($item['class']))

        @php   $i++; @endphp

        <a class='hr' id='menu-name-{{ $i }}'>{{$item['name']}}</a>

        @php continue; @endphp

    @endif
    <li class="menu-element-{{$i}}">
        <a class="list-link{{ Route::current()->uri() == $item['uri'] ? ' visited' : '' }}" href="/{{$item['uri']}}">
        <img src="https://zebra-target.ru/PNG/{{$item['img']}}" width="16" height="16"> {{$item['name']}}</a>
    </li>
@endforeach
</ul>
</div>
