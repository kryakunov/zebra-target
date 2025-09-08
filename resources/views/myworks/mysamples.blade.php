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


@forelse($data as $item)

<div class="mywork"> 
    <div class="row">
        
    <div class="col-md-8">
    <div class="myworkheader">

                <span class="myworktitle">{{ $item->name }}</span>
    
        </div>
        <div class="myworkabout">
            @php $str  = ''; @endphp
            @foreach($item->getAll as $sample)
                @php 
                    $str .= $sample->WorkType->type_name . ' > ';
                @endphp 
            @endforeach
            @php
                $str = trim ($str, ' >');
                echo $str;
            @endphp
        </div> 
    </div> 
    <div class="col-md-2 middle">

    </div>
    <div class="col-md-2 middle">

    </div>

    </div>

</div>
@empty
    <br><h6>Здесь будут отображаться все ваши задачи по сбору целевой аудитории, их процент выполнения и результат.</h6>
@endforelse
     
    </div>
</div> 



@endsection