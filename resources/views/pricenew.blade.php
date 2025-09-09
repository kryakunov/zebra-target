@extends('layout')

@section('title') @parent Цены и тарифы @endsection

@section('content')
<div class="tab">
<div class="title">Цены и тарифы</div>
<div class="content">

@include('errors.session')

<?php

$MERCHANT_ID   = 10139;                    // ID магазина
$SECRET_WORD   = 'Rz-KqbbAwslJG0LHKFfuHbYwXvAb3Auh';   // Секретный ключ
$PAYMENT_ID    = session('id').'_'.time();  // ID заказа (мы используем time(), чтобы был всегда уникальный ID)

// $ORDER_AMOUNT1  = 199;
$PAYMENT_ID1 = $PAYMENT_ID .'_1';
$sign1 = md5($MERCHANT_ID.':'.$ORDER_AMOUNT1.':'.$SECRET_WORD.':'.$PAYMENT_ID1);

//$ORDER_AMOUNT2  = 399;
$PAYMENT_ID2 = $PAYMENT_ID .'_2';
$sign2 = md5($MERCHANT_ID.':'.$ORDER_AMOUNT2.':'.$SECRET_WORD.':'.$PAYMENT_ID2);

//$ORDER_AMOUNT3  = 599;
$PAYMENT_ID3 = $PAYMENT_ID .'_3';
$sign3 = md5($MERCHANT_ID.':'.$ORDER_AMOUNT3.':'.$SECRET_WORD.':'.$PAYMENT_ID3);

// $ORDER_AMOUNT4  = 1299;
$PAYMENT_ID4 = $PAYMENT_ID .'_4';
$sign4 = md5($MERCHANT_ID.':'.$ORDER_AMOUNT4.':'.$SECRET_WORD.':'.$PAYMENT_ID4);

?>


<div class="row">
   <div class="col-md-3">
   <div class='tab-title-price'><?=$ORDER_AMOUNT1?><br><span class='priceAbout'>руб / 1 месяц</span></div>
      <div class='tab-price'>
      Полный доступ <hr> Безлимитный парсинг <hr> Отслеживание <label><b>5</b></label> групп <hr><label>До <b>5</b></label> баз пользователей
      </div>
      <div class='tab-title-price-bottom'>
         <form method='get' action='https://api.mivion.com/pay'>
            <input type='hidden' name='m' value='<?=$MERCHANT_ID?>'>
            <input type='hidden' name='oa' value='<?=$ORDER_AMOUNT1?>'>
            <input type='hidden' name='o' value='<?=$PAYMENT_ID1?>'>
            <input type='hidden' name='s' value='<?=$sign1?>'>
            <input type="submit" class="btn btn-warning" <?php if (!session('id')) echo 'disabled'; ?> value="Оплатить">
         </form>
      </div>
   </div>
   <div class="col-md-3">
   <div class='tab-title-price'><?=$ORDER_AMOUNT2?> <br><span class='priceAbout'>руб / 3 месяца</span></div>
      <div class='tab-price'>
      Полный доступ <hr> Безлимитный парсинг <hr> Отслеживание <label class='text-size-13'><b>10</b></label> групп <hr><label class='text-size-13'>До <b>10</b></label>  баз пользователей
      </div>
      <div class='tab-title-price-bottom'>
         <form method='get' action='https://api.mivion.com/pay'>
            <input type='hidden' name='m' value='<?=$MERCHANT_ID?>'>
            <input type='hidden' name='oa' value='<?=$ORDER_AMOUNT2?>'>
            <input type='hidden' name='o' value='<?=$PAYMENT_ID2?>'>
            <input type='hidden' name='s' value='<?=$sign2?>'>
            <input type="submit" class="btn btn-warning" <?php if (!session('id')) echo 'disabled'; ?> value="Оплатить">
         </form>
      </div>
   </div>
   <div class="col-md-3">
   <div class='tab-title-price'><?=$ORDER_AMOUNT3?> <br><span class='priceAbout'>руб / 6 месяцев</span></div>
      <div class='tab-price'>
      Полный доступ <hr> Безлимитный парсинг <hr> Отслеживание <label class='text-size-14'><b>20</b></label> групп <hr><label class='text-size-14'>До <b>20</b></label>  баз пользователей
      </div>
      <div class='tab-title-price-bottom'>
         <form method='get' action='https://api.mivion.com/pay'>
            <input type='hidden' name='m' value='<?=$MERCHANT_ID?>'>
            <input type='hidden' name='oa' value='<?=$ORDER_AMOUNT3?>'>
            <input type='hidden' name='o' value='<?=$PAYMENT_ID3?>'>
            <input type='hidden' name='s' value='<?=$sign3?>'>
            <input type="submit" class="btn btn-warning" <?php if (!session('id')) echo 'disabled'; ?> value="Оплатить">
         </form>
      </div>
   </div>
   <div class="col-md-3">
   <div class='tab-title-price'><?=$ORDER_AMOUNT4?> <br><span class='priceAbout'>руб / год</span></div>
      <div class='tab-price'>
      Полный доступ <hr> Безлимитный парсинг <hr> Отслеживание <label class='text-size-15'><b>50</b></label> групп <hr><label class='text-size-15'>До <b>50</b></label>  баз пользователей
      </div>
      <div class='tab-title-price-bottom'>
         <form method='get' action='https://api.mivion.com/pay'>
            <input type='hidden' name='m' value='<?=$MERCHANT_ID?>'>
            <input type='hidden' name='oa' value='<?=$ORDER_AMOUNT4?>'>
            <input type='hidden' name='o' value='<?=$PAYMENT_ID4?>'>
            <input type='hidden' name='s' value='<?=$sign4?>'>
            <input type="submit" class="btn btn-warning" <?php if (!session('id')) echo 'disabled'; ?> value="Оплатить">
         </form>
      </div>
   </div>
</div>
<hr>
Если вы не нашли подходящего способа оплаты - напишите мне в личные сообщения <a href="https://vk.com/im?sel=185466160" target="_blank">ВКонтакте</a>

<br><br>
<div class="row">
   <div class="col-md-6">
      <form class="parser" action="{{route('price')}}" method="POST">
      @csrf
      <label class='label'><b>Есть промокод?</b> Вставьте его сюда:</label>
         <div class="input-group">
            <input type="text" name="promo" class="form-control" placeholder="Промокод">
            <span class="input-group-btn">
               <input class="btn btn-success" type="submit" <?php if (!session('token')) echo 'disabled'; ?> type="submit" id="btnMenu" value="Применить">
            </span>
         </div>
      </form>
   </div>
</div>

</div>
</div>

@endsection
