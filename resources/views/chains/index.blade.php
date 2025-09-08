@extends('layout')

@section('title') @parent Цепочки @endsection
<?php date_default_timezone_set('Europe/Moscow'); ?>
@section('content')
<div class="tab">
<div class="title">Цепочки задач</div>
<div class="content">

<input type="hidden" id="vk_id" value="{{ (isset($data[0])) ? $data[0]->vk_id : '' }}">

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

<!-- <a href="{{route('createsample')}}" class="btn btn-outline-dark btn-sm" >+ Создать цепочку</a> -->



@forelse($chains as $item)

<div class="row chain-index"> 
        <div class="col-md-9">       
            <a href={{ route('chainshow', $item->id) }} class="chainTitle">{{ $item->name }}</a> &nbsp;
            <div class='time' style='padding: 20px; padding-left: 10px'>
                <?= $item['description'] ?>
            </div>
            <?= $item['desc'] ?>
<br><br>
        </div>
        <div class="col-md-3">  

            <label class="label right">
                <img src="{{ $item->User->photo }}"  width="30" class="circle"> {{ $item->User->first_name }} {{ $item->User->last_name }}

            </label>

            <br>

        </div>
</div>


@empty
    No Data.
@endforelse
<br><br>
    </div>
</div> 






@endsection