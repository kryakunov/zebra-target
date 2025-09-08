@extends('layout')

@section('title') @parent Вывод средств @endsection

@section('content')
<div class="tab">
<div class="title">Оплатить полный доступ</div>
<div class="content">

@include('errors.exceptions')
@include('errors.session')
@include('errors.validate')


<a class="btn btn-outline-success btn-sm" href="{{route('partner')}}"><< Вернуться назад</a><br><br>


<form action="{{route('pay-access')}}" method="post" class="mb-4">
@csrf

    <div class='row'>
      <div class='col-md-6'>
         <label class='label'><b>Выберите пакет:</b></label>
         <select name='package' class='form-control' >
            <option value='1'>1 месяц - 199 руб</option>
            <option value='2'>3 месяца - 399 руб</option>
            <option value='3'>6 месяцев - 599 руб</option>
            <option value='4'>1 год - 1299 руб</option>
         </select>
         <br>
         <input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Оплатить" onclick="return confirm('Вы уверены?')">
      </div>
      </div>



</form> 


@endsection