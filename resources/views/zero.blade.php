@extends('layout')

@section('title') @parent Цены и тарифы @endsection

@section('content')
<div class="tab">
<div class="title">Цены и тарифы</div>
<div class="content">

@include('errors.session')


<div class="row">
   <div class="col-md-3">

   <div class='tab-title-price'>0<br><span class='priceAbout'>руб / 1 месяц</span></div>
      <div class='tab-price'>      
      Полный доступ <hr> Безлимитный парсинг <hr> Отслеживание <label><b>5</b></label> групп <hr><label>До <b>5</b></label> баз пользователей
      </div>
      <div class='tab-title-price-bottom'>
         <form method='post' action='https://zebra-target.ru/zero-pay'>
            @csrf
            <input type='hidden' name='id' value="{{ session('id') }}">
            <input type="submit" class="btn btn-warning" <?php if (!session('id')) echo 'disabled'; ?> value="Оплатить">
         </form>
      </div>
   </div>
 

</div>
</div>

@endsection