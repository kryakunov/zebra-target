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

<a href="{{route('createsample')}}" class="btn btn-outline-dark btn-sm" >+ Создать шаблон</a> 


@forelse($data as $item)





<div class="mywork"> 
    <div class="row">
         
    <div class="col-md-9">
        <div class="myworkheader">

        <span class="myworktitle"><a href="{{ route('sampleshow', $item->id) }}">{{ $item->name }}</a></span><br>
        <span class="result_format workPercent">
            @if($item->status == 1) Запущен @endif
        </span>
        </div>
        @include('samples._views')
    </div> 


    <div class="col-md-3 middle">
    <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal{{ $item->id }}" class="right">Настройки запуска</a><br>
        <a href="{{route('sharesample', $item->id )}}" class="right">Поделиться шаблоном</a><br>
    <a href="{{ route('sampledelete', $item->id) }}" class="right" onclick="return confirm('Удалить шаблон?')">Удалить</a>
    </div>

    </div>
    <div class="myworkabout">
        <label  class="time">
            @php $str  = ''; @endphp
            @foreach($item->getWorks as $sample)
                @php 
                    $str .= $sample->WorkType->type_name . ' > ';
                @endphp 
            @endforeach
            @php
                $str = trim ($str, ' >');
                echo $str;
            @endphp
    </label>
        </div> 

</div>





<div class="modal fade" id="exampleModal{{ $item->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Настройки запуска</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">

    <form action="{{ route('SaveSampleTimer', $item->id) }}" method="get" class="mb-4">
    @csrf

    <div class="form-check form-switch">
    <br><label class="form-check-label" for="flexSwitchCheckChecked{{$item->id}}">
        <input name="status" class="form-check-input myradio" type="checkbox" id="flexSwitchCheckChecked{{$item->id}}" {{ ($item->status == 1) ? 'checked' : '' }}>
        Запускать по таймеру </label><br><br>


        Запускать раз в
        <select name="timer" class="textbox">
            <?php 
            for($i = 1; $i < 8; $i++) {
                echo "<option value='".$i."' ";
                if ($i == $item->timer) echo "selected='selected'";
                echo " >$i</option>";
            }
            ?>
        </select> дней


        
      </div>

           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <input type="submit" class="btn btn-primary" value="Сохранить">
    </form>
      </div>
    </div>
  </div>
</div>




@empty
    <br><h6>Здесь будут отображаться все ваши шаблоны.</h6>
@endforelse
     
    </div>
</div> 






@endsection