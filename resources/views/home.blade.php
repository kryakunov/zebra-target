@extends('layout')

@section('content')

<div class="tab">

<div class="title">Поиск и сбор целевой аудитории во ВКонтакте.</div>
<div class="content">
@include('errors.session')

<!--
<h4>Впервые на сайте?</h4><br>
<h5>Дарим 2 дня полного доступа за регистрацию </h5><b><a href="{{route('vkLogin')}}">Войти через ВК</a></b> <br><hr><br>

-->
Добро пожаловать на сервис! <br><br>

Зебра Таргет — инструмент для парсинга целевой аудитории из ВКонтакте. С помощью нашего сервиса можно искать целевую аудиторию для своего бизнеса, парсить пользователей, собирать базы для ретаргетинга и т.д.
<br><br>
Например, можно собирать сообщества, на которые подписана ваша целевая аудитория, собирать в них активность, отслеживать в них новых участников и так далее.
<br><br>
Вступайте в нашу <a href="http://vk.com/zebratarget_ru" target="_blank"><b>группу ВК</b></a>
<br>

<br>
 <!-- <a href="https://youtu.be/-Ou1NoItTP0" target="_blank"><b>Видео-обзор сервиса</b></a> -->

 <iframe width="560" height="315" src="https://www.youtube.com/embed/-Ou1NoItTP0?si=x_4OV_QF8vnU5op0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>


<!--<div id="vk_groups"></div>
<script type="text/javascript">
VK.Widgets.Group("vk_groups", {mode: 3, width: "600"}, 127807953);
</script>-->

</div>
</div>


@endsection
