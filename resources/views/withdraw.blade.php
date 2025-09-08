@extends('layout')

@section('title') @parent Вывод средств @endsection

@section('content')
<div class="tab">
<div class="title">Вывод средств</div>
<div class="content">

@include('errors.exceptions')
@include('errors.session')
@include('errors.validate')


<a class="btn btn-outline-success btn-sm" href="{{route('partner')}}"><< Вернуться назад</a><br><br>


<form action="{{route('withdrawStore')}}" method="post" class="mb-4">
@csrf

    <div class='row'>
      <div class='col-md-6'>
         <label class='label'><b>Платежная система:</b></label>
         <select name='system' class='form-control' >
            <option value='yandex'>Яндекс</option>
            <option value='tinkoff'>Тинькофф</option>
         </select>
         <br>
         <label class='label'><b>Платежные реквизиты:</b></label><br>
         <input type='text'  name='req' class='form-control' placeholder=''  value="{{ old('req') }}" aria-describedby='button-addon2' /> 
         <br>
         <label class='label'><b>Сумма вывода:</b></label><br>
         <input type='text'  name='summ' id='time_min' class='form-control' value="{{ old('summ') }}" aria-describedby='button-addon2' /> 
         <a style="cursor: pointer; color: #4185B8;" onclick="datachange('{{$balance}}')">все доступные средства</a>
         <br><br>
         <input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Создать заявку" onclick="return confirm('Вы уверены?')">
      </div>
      </div>



</form> 


@endsection