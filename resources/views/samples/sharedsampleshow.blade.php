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


<a class="btn btn-outline-dark btn-sm" href="{{ route('mysamples') }}"><< Назад</a><br><br>


<h4><center>{{ $data->name }}</center></h4>

<img src="{{ $data->User->photo }}" width="35" height="35" class="circle"> {{ $data->User->first_name }} {{ $data->User->last_name }} (Автор шаблона)
<span class="right">{{ $data->views }} просмотров</span><br><br>

    @php $i = 0; @endphp
    @foreach($data->getAll as $item)

    <div class="mywork"> 

        <p>
            Шаг {{ ++$i }}
            <a data-bs-toggle="collapse" href="#t{{$item->id}}" role="button" aria-expanded="false" aria-controls="t{{$item->id}}">
                <span class="myworktitle">
                    {{ ($item->name == '') ? $item->WorkType->type_name : $item->name }}
                </span>
            </a>
        </p>


    <div class="collapse" id="t{{$item->id}}">
    Настройки парсера:
            <div class="card card-body">
            
            <form action="{{route('savesamplerequest')}}" method="post" class="mb-4">
                @csrf
                <input type="hidden" name="id" value="{{$item->id}}">
                
                <?php
                $type = 'forms.'.$item->WorkType->type;
                ?>
                
                @include($type, ['request' => unserialize($item->request)])

                <hr>
            <input type="submit" value="Сохранить" class="btn btn-success btn_size">
            </form>

            </div><br>
        </div>
        </div> 
    @endforeach

</div> 




@endsection