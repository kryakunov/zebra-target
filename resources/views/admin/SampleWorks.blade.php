@extends('admin-layout')

@section('content')



@if(session('success'))
    <div class="alert alert-success" role="alert">
        {{session('success')}}
    </div> 
@endif

@if(session('danger'))
    <div class="alert alert-danger" role="alert">
        {{session('danger')}}
    </div> 
@endif


 
@if(count($works) > 0)
<div class="tab">
<div class="title">Последние шаблоны пользователей</div>
    <div class="content">

    <a href="{{ route('admin3') }}" class="btn btn-outline-dark btn-sm" >Показать только клиентов</a>
    <a href="{{ route('admin3', ['all' => 'true']) }}" class="btn btn-outline-dark btn-sm" >Показать все</a> 
    <a href="{{ route('admin3', ['error' => true]) }}" class="btn btn-outline-dark btn-sm" >Показать только с ошибками</a> <br><br>


 <!--
    <a href="{{ route('admin3', ['errors' => true]) }}" class="btn btn-outline-dark btn-sm" >Только задания с ошибками</a> <br><br>
-->
@foreach($works as $item)

<?php 

$logfile = '../storage/app/samplelogs/'.$item->sample_id . '.txt';
$sourceFile = '../storage/app/sourceworks/'.$item->vk_id.'_'.$item->date . '.txt';

if (!file_exists($logfile)) $logfile = null;
if (!file_exists($sourceFile)) $sourceFile = null;

date_default_timezone_set('Europe/Moscow');
    if(isset($_GET['error']))
    {
        $streams = $item->stream->toArray(); 
        $error = false;
        foreach ($streams as $stream) {
            if(isset($stream['error'])) {
                $error = true;
            }
        }
        if ($error == false) continue;
    }

    if (!isset($_GET['all'])) {
        $admins = ['573204714', '18277740', '185466160'];
        if (in_array($item->vk_id, $admins)) continue;
    }
?>

<div class="mywork"> 
    <div class="row">
    <div class="col-md-6">
        <div class="myworkheader">
            @if($item['status'] == 1)
                <a href="#" class="myworktitle">
                  {{ $item->name }}</a><span class="percent"> ({{$item->percent}}%) </span><small>ID: {{ $item->id }}</small>
            @elseif($item['status'] == 9)
                <a href="{{route($item->WorkType->route, $item['id'])}}" class="myworktitle">{{ $item->name }}</a><span class="percent"> задача остановлена ({{$item->percent}}%) </span>
            @else
                <div class="workTitle">{{$item->name}} <span class="percent"> ({{$item->percent}}%)</span><small>ID: {{ $item->id }}</small> </div> 
            @endif
        </div>
        <div class="myworkabout">
            <div class="myworkname"><img src="https://zebra-target.ru/PNG/icon_cloud_alt.png" width="16" height="16"> {{ $item->WorkType->type_name }}</div>
            <div class="myworkname">
                <img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="16" height="16"> 
                {{ ($item->count) ? $item->count : '0' }} 
                {{ ($item->WorkType->type_desc == 'users') ? 'пользователей' : '' }}
                {{ ($item->WorkType->type_desc == 'groups') ? 'сообществ' : '' }}
                {{ ($item->WorkType->type_desc == 'socialnetworks') ? 'контактов' : '' }}
            </div>
            <div class="time">
                <img src="https://zebra-target.ru/PNG/icon_calendar.png" width="13" height="13">
                {{ date('d.m.y H:i', $item->date) }}  

                @if($item->status == 1)
                    <img src="https://zebra-target.ru/PNG/icon_clock_alt.png" width="13" height="13">
                    {{ gmdate("i:s", ($item->date_end - $item->date_start)) }}
                @endif
                </div>
        </div>
    </div>
    <div class="col-md-3 myworkname">
        @if(isset($logfile))
            <a href="{{ route('getsamplelog', ['id' => $item->sample_id] ) }}">Log</a>
        @endif
        <br>
        @if(isset($sourceFile))
            <a href="{{ route('downloadsourcefile', ['id' => $item->id] ) }}">Исходные данные</a>
        @endif
        <br>
    </div>    
    <div class="col-md-3 myworkname">
    </div>
    </div>
</div>

        @endforeach

    </div>
</div> 
@endif


@endsection