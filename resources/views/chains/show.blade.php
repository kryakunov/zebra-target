@extends('layout')

@section('title') @parent Цепочки задач @endsection
<?php date_default_timezone_set('Europe/Moscow'); ?>
@section('content')
<div class="tab">
<div class="title">{{ $data->name }}</div>
<div class="content">

<input type="hidden" id="vk_id" value="{{ (isset($data[0])) ? $data[0]->vk_id : '' }}">

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

<a class="btn btn-outline-dark btn-sm" href="{{ route('chains') }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
    </svg>
    Назад</a>

<a class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#timerModal{{ $data->id }}" href="">
    Запустить цепочку
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-square" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm4.5 5.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/>
    </svg>
</a>
<br><br>

{{ $data->description }} <br><br>

<form action="{{route('savesampledatafrom')}}" method="post" class="mb-4">
@csrf

<input type="hidden" id="sampleid" name="id" value="{{ $data->id }}">
<div> 


    @php $i = 0; @endphp
    @foreach($steps as $item)

    <div class="mywork"> 
    <div class="row"> 
        <div class="col-md-8">
            <p>
                Шаг {{ ++$i }}
                <a data-bs-toggle="collapse" href="#t{{$i}}" role="button" aria-expanded="false" aria-controls="t{{$i}}">
                    <span class="myworktitle">
                        {{ $item->type_name  }} 

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

    </div>

    <div class="collapse" id="t{{$i}}">
    <br>Настройки парсера:<br>
            <div class="card card-body">
            
                <form action="{{route('savesamplerequest')}}" method="post" class="mb-4">
                    @csrf
                    <input type="hidden" name="id" value="{{$item->id}}">
                    
                    <?php
                    $type = 'forms.'.$item->type;
                    ?>
    
                    @include(mb_strtolower($type), ['request' => unserialize($item->request)])

                    <hr>
                <input type="submit" value="Сохранить" class="btn btn-success btn_size">
                </form>

            </div><br>
        </div>
    </div> 
    @endforeach



</div> </div> </div> 



<div class="modal fade bd-example-modal-lg" style="width: 100%;"  id="timerModal{{ $data->id }}" tabindex="-1" aria-labelledby="timerModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="timerModalLabel">Настройки запуска</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">

        <!------ ---->
        <form action="{{ route('chainrun', $data->id) }}" method="post" class="mb-4">
            @csrf

            @include('forms._users')

        <!------>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <input type="submit"class="btn btn-success btn_size left" type="submit"  id="btnMenu" value="Запустить цепочку"  onclick="change()">
        </form> 
    </form>
      </div>
    </div>
  </div>
</div>


<div class="tab">
<div class="title">Комментарии</div>
<div class="content">

<div class=""><b>Andrey</b><br>Нормальная цепочка!!1</div><br>
<div class=""><b>Andrey</b><br>Нормальная цепочка!!1</div><br>
<div class=""><b>Andrey</b><br>Нормальная цепочка!!1</div><br>

</div> 
@endsection