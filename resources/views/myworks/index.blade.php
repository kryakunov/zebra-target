@extends('layout')

@section('title') @parent Менеджер задач @endsection
<?php date_default_timezone_set('Europe/Moscow'); ?>
@section('content')
<div class="tab">
<div class="title">Менеджер задач</div>
<div class="content">


 
<input type="hidden" id="vk_id" value="{{ (isset($data[0])) ? $data[0]->vk_id : '' }}">

@if(count($data)>0)


<a href="{{route('myworks')}}" class="btn btn-outline-dark btn-sm" >
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-repeat" viewBox="0 0 16 16">
  <path d="M11 5.466V4H5a4 4 0 0 0-3.584 5.777.5.5 0 1 1-.896.446A5 5 0 0 1 5 3h6V1.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384l-2.36 1.966a.25.25 0 0 1-.41-.192m3.81.086a.5.5 0 0 1 .67.225A5 5 0 0 1 11 13H5v1.466a.25.25 0 0 1-.41.192l-2.36-1.966a.25.25 0 0 1 0-.384l2.36-1.966a.25.25 0 0 1 .41.192V12h6a4 4 0 0 0 3.585-5.777.5.5 0 0 1 .225-.67Z"/>
</svg>
Обновить</a> 
@if(session('id'))
<a href="{{route('deleteallworks')}}" onclick="return confirm('Вы уверены?')" class="btn btn-outline-dark btn-sm" >
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
</svg>
Удалить все задачи</a> <br><br>
@endif

<label class="label">* все задачи хранятся не более 30 суток с момента их последнего запуска</label><br>

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')



<?php
date_default_timezone_set('Europe/Moscow');

$admins = ['52090716', '573204714', '185466160', '18277740', '52090716'];


?>

@if(session('chain'))
    <br><span class='chainshow'>{{ session('chain') }} <a href="{{route('myworks')}}">Сбросить</a></span> <br><br>
 
    <?php
    if (in_array(session('id'), $admins)): ?>
    <a href="" data-bs-toggle="modal" data-bs-target="#sampleModal"  class="btn btn-outline-dark btn-sm">Сохранить шаблон</a>
    <br><br>
    @include('myworks._savesample')
    <? endif; ?>
@endif

@endif

@php $i = 0; @endphp
@forelse($data as $item)

<div class="mywork"> 
    <div class="row">
        
    <div class="col-md-8">
    <div class="myworkheader">
    @if(session('chain'))
        Шаг {{ ++$i }}
    @endif
            @if($item['status'] == 1)
                <span class="myworktitle">
                @if($item->WorkType->type_desc == 'users')
                    <a href="{{ route('tool1', ['id' => $item->id]) }}" class="myworktitle">{{ $item->name }}</a>
                @elseif($item->WorkType->type_desc == 'groups')
                    <a href="{{ route('showgroups', ['id' => $item->id]) }}" class="myworktitle">{{ $item->name }}</a>
                @elseif($item->WorkType->type_desc == 'socialnetworks')
                    <span class="myworktitle">{{ $item->name }}</span>
                @endif
                </span>
    
                @if($item->parent_id) 
                    <a href="{{route('getworkchain', ['id' => $item->id])}}" class="chain">Показать цепочку</a>
                @endif
      
            @elseif($item['status'] == 9)
            <span class="myworktitle">{{ $item->name }}</span><span class="percent"> остановлено ({{$item->percent}}%) </span>

            @else
                <div class="workTitle">{{$item->name}}
  
            </div>
                 
            @endif

        </div>
        <div class="myworkabout" style="padding-left: 13px">
            <div class="myworkname">
                <!--<img src="https://zebra-target.ru/PNG/icon_cloud_alt.png" width="14" height="14"> -->
                @if($item['status'] == 1)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                </svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hourglass" viewBox="0 0 16 16">
                    <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2z"/>
                </svg>
                @endif
                {{ $item->WorkType->type_name }}</div>
                @if($item['status'] == 1)
                <div class="myworkname">
                        <div class="time">
                        <!--<img src="https://zebra-target.ru/PNG/icon_calendar.png" width="13" height="13">-->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar" viewBox="0 0 16 16">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                        </svg>
                        {{ date('d.m', $item->date_start) }} 

                        &nbsp;
                        <!--<img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="14" height="14">-->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-text" viewBox="0 0 16 16">
                        <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                        <path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8m0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5"/>
                        </svg>
                        {{ ($item->count) ? $item->count : '0' }} 
                        {{ ($item->WorkType->type_desc == 'users') ? 'пользователей' : '' }}
                        {{ ($item->WorkType->type_desc == 'groups') ? 'сообществ' : '' }}
                        {{ ($item->WorkType->type_desc == 'socialnetworks') ? 'контактов' : '' }}
                        &nbsp;
                        @if($item->status == 1)
                            <!--<img src="https://zebra-target.ru/PNG/icon_clock_alt.png" width="13" height="13">-->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                            <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                            <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                            <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                            </svg>
                            {{ gmdate("i:s", ($item->date_end - $item->date_start)) }}
                        @endif

                        @if($item->timer == 1)
                        <br><br>
                            <a href="" class="autorun" data-bs-toggle="modal" data-bs-target="#timerModal{{ $item->id }}" class="dropdown-item">
                            <img src="https://zebra-target.ru/PNG/icon_hourglass.png" width="13" height="13">
                                Автозапуск раз в {{ $item->timer_count }} @if($item->timer_count == 1) день @elseif($item->timer_count > 4) дней @else дня @endif 
                            </a>
                        @endif
                </div>     
            </div>
                @endif

     
        </div> 
    </div> 
    <div class="col-md-2 middle">
        <div class="live{{$item['id']}}" id="live{{$item['id']}}" style="display:none;">
            <a href="{{route('killmywork', $item['id'])}}" class="noborder right" onclick="return confirm('Вы уверены?')">Остановить</a>
        </div>

       @if($item['status'] == 1)
       
            @include('myworks._use')

        @elseif($item['status'] == 9 )

            <a href="{{route('workdelete', ['id' => $item['id']])}}" class="right noborder" onclick="return confirm('Вы уверены?')">Удалить задачу</a><br>
            <a href="{{ route('workrun', ['id' => $item->id] ) }}" class="right noborder" onclick="return confirm('Вы уверены?')">Перезапустить</a>

          
        @elseif($item['status'] == 0 && (time() - $item['date']) > 3600 )
            <a href="{{route('workdelete', ['id' => $item['id']])}}" class="right noborder">Удалить задачу</a><br>
            <a href="{{ route('workrun', ['id' => $item->id] ) }}" class="right noborder" onclick="return confirm('Вы уверены?')">Перезапустить</a>
        @endif
    </div>
    <div class="col-md-2 middle">
        @if($item['status'] == 1)
            @include('myworks._actions')
        @endif
    </div>
    @include('myworks._views')
    </div>
    @include('myworks._checkaccess')

    @if($item->status != 1 && $item->status != 9)
    <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
        <div id="progress-{{$item->id}}" class="progress-bar progress-bar-striped progress-bar-animated" style="width: {{$item->percent}}%">{{$item->percent}}%</div>
    </div>
    @endif
</div>
@empty
    <br><h6>Здесь будут отображаться все ваши задачи по сбору целевой аудитории.</h6>
@endforelse
     
    </div>
</div> 


<script type="text/javascript">

    function mode() 
    {

        $.ajax({

            url: 'ajax',
            success: function(data) {

                var res = JSON.parse(data);

                res.forEach(function(item, index, res){

                    var p = document.getElementById('progress-'+item.id);

                    p.innerHTML = item.percent + "%";
                    p.setAttribute("style", "width: " + item.percent + '%');

                //   if (item.live == true) {
                //       $('#live'+item.id).show();
                //    }


                    if (item.status == 1) {
               
                        location.reload();
                    }
                });
            }
        });
    };

    const element1 = document.querySelector(`[id^="progress-"]`);
if (element1 != null){
    setInterval(mode, 2000);
};

</script>

@endsection