@extends('layout')

@section('title') @parent Мои шаблоны @endsection
<?php date_default_timezone_set('Europe/Moscow'); ?>
@section('content')
<div class="tab">
<div class="title">Мои шаблоны</div>
<div class="content">



<input type="hidden" id="vk_id" value="{{ (isset($data[0])) ? $data[0]->vk_id : '' }}">


@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')


<a class="btn btn-outline-dark btn-sm" href="{{ route('mysamples') }}"><< Назад</a>
<a class="btn btn-outline-dark btn-sm" href="{{ route('samplerun', $data->id) }}">Запустить шаблон</a>
<br><br>
 
<h4>{{ $data->name }}</h4>

@if($data->date_update != null)
<img src="https://zebra-target.ru/PNG/icon_calendar.png" width="13" height="13"> Последний запуск: 
{{ date("d.m H:i", $data->date) }}  
<br>
<img src="https://zebra-target.ru/PNG/icon_clock_alt.png" width="13" height="13"> Выполнено за: 
{{ gmdate("i:s", ($data->date_end - $data->date_start)) }}
@endif
<br><br>
<form action="{{route('savesampledatafrom')}}" method="post" class="mb-4">
@csrf

<input type="hidden" id="sampleid" name="id" value="{{ $data->id }}">
<div class="mywork"> 

        <a data-bs-toggle="collapse" href="#choose" role="button" aria-expanded="false" aria-controls="choose">
        <span class="myworktitle">
            Исходные данные для шаблона:</a>
        </span>  

            @if(isset($data->getWorks->first()->data_from) && isset($source))

                @if($data->getWorks->first()->data_from[0] == 0)
                    Из моих задач: <label class='label'>{{ $source->name }} ({{ $source->count }} ID)</label>
                @elseif($data->getWorks->first()->data_from[0] == 1)
                    Из списков: <label class='label'>{{ $source->name }} ({{ $source->count }} ID)</label>
                @endif
            @else
                Не выбрано
            @endif

            <br><br>

<div class="collapse" id="choose">

        <div class="">
        <label class='label'>Откуда берем?</label>
        <div class="result_format">
        <div>
            <label>
                <input  class="choose" data-val="true"  id="ProjectBind_ProjectBindType" name="data_from" type="radio" value="0" checked/> Из моих задач&emsp;
            </label>
        </div>
        <div>
            <label>
                <input  class="choose" data-val="true"  id="ProjectBind_ProjectBindType" name="data_from" type="radio" value="1" /> Из моих списков&emsp;
            </label>
        </div>
       <!-- <div>
            <label>
                <input class="choose" data-val="true"  id="ProjectBind_ProjectBindType" name="data_from" type="radio" value="2" /> Из формы&emsp;
            </label>
        </div>-->
        </div>
        <div class="form-group no-margin" style="width: 100%;">
            <div id="works">
                <select class="form-control"  name="works">
                    <option value="0">- Выберите задачу -</option>
                    @foreach($works as $work)
                        <option value="{{ $work->id }}">{{ $work->name }} ({{ $work->count }} ID)</option>
                    @endforeach

                </select>
            </div> 
            <div id="lists" style="display:none;">
                <select class="form-control" name="lists">
       
                    <option value="0">- Выберите список -</option>
                    @foreach($lists as $list)

                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->count }} ID)</option>
                    @endforeach
                </select>


            </div>
            <div id="form" style="display:none;">
                <div class="form-group mb-3">
                    <textarea class="output-panel form-control "  name="data" rows="5" placeholder="Вставьте данные по одному значению на строку"></textarea>
                </div>
            </div>
        </div><br>
        <input type="submit" value="Сохранить" class="btn btn-success btn_size">
        </form>

        </div>
        <br>
    </div>
</div> 

<script type="text/javascript">

    $('input.choose').change(function () {
        if ($(this).val() == "0") {
            $('#works').show();
            $('#lists').hide();
            $('#form').hide();
        } if ($(this).val() == "1") {
            $('#works').hide();
            $('#form').hide();
            $('#lists').show();
        } if ($(this).val() == "2") {
            $('#works').hide();
            $('#lists').hide();
            $('#form').show();
        }
    });
</script>


    @php $i = 0; @endphp
    @foreach($data->getWorks as $item)

    <div class="mywork"> 
    <div class="row">
        <div class="col-md-8">
            <p>
                Шаг {{ ++$i }}
                <a data-bs-toggle="collapse" href="#t{{$item->id}}" role="button" aria-expanded="false" aria-controls="t{{$item->id}}">
                    <span class="myworktitle">
                        {{ ($item->name == '') ? $item->WorkType->type_name : $item->name }} 

                    </span>
                </a>

            </p>
        </div>
        <div class="col-md-3">
        @if(isset($item->date))
        <label class="time">
            @if($item->percent > 0 && $item->percent < 100)
                <label  class="time">
                    {{ $item->state }} 
                    {{ $item->percent }}% 
                </label><br>
            @else
                <img src="https://zebra-target.ru/PNG/icon_clock_alt.png" width="13" height="13">
                {{ gmdate("i:s", ($item->date_end - $item->date_start)) }}
                <img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="14" height="14"> 
                {{$item->count}}
             @endif
             
        </label>
        @endif
        </div>
        <div class="col-md-1">
            @if($i == 1)
                <a href="{{route('deletestep', $data->id)}}">Удалить</a>
            @endif</div>
    </div>

 

    <div class="collapse" id="t{{$item->id}}">
    <br>Настройки парсера:<br>
            <div class="card card-body">
            
            <form action="{{route('savesamplerequest')}}" method="post" class="mb-4">
                @csrf
                <input type="hidden" name="id" value="{{$item->id}}">
                
                <?php
                $type = 'forms.'.$item->WorkType->type;
                ?>
                
                @include(mb_strtolower($type), ['request' => unserialize($item->request)])

                <hr>
            <input type="submit" value="Сохранить" class="btn btn-success btn_size">
            </form>

            </div><br>
        </div>
    </div> 
    @endforeach

    @if($item->count > 0)
    <div class="mywork"> 
        <div class="row">
            <div class="col-md-8">
                Собрано: {{ $item->count }} 
                
                @if($item->WorkType->type_desc == 'users') пользователей 
                @elseif($item->WorkType->type_desc == 'groups') сообществ 
                @elseif($item->WorkType->type_desc == 'posts') постов
                @elseif($item->WorkType->type_desc == 'socialnetworks') соц. сетей
                @endif
                 
            </div>
            <div class="col-md-4">
                    @include('samples._actions')
            </div>
        </div>
    </div>
    @endif

</div> 



@endsection