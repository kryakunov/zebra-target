@extends('admin-layout')

@section('content')

<?php
  if (isset($_GET['pid'])) {
    session(['id' => $_GET['pid']]);
    session(['photo' => '']);
  }
?>

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

@if(count($questions) > 0)

<div class="tab">
    Новых вопросов: <b>{{ count($questions) }}
    <a href="{{route('admin2')}}">Ответить</a></b>
</div>

@endif

Свободных токенов: {{ $countTokens }}

@if(count($withdraws) > 0)
<div class="tab">
<div class="title">Заявки на вывод: <b>{{count($withdraws)}}</b></div>
    <div class="content">
        <table class='table'>
            <thead>
                <tr>
                    <th class='ShowGroupName'>Реквизиты</th>
                    <th class='ShowGroupName'>Сумма</th>
                    <th class='ShowGroupName'>Статус</th>
                    <th class='ShowGroupName'>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($withdraws as $withdraw)
                    <tr>
                        <td class='ShowGroupName'>{{$withdraw['method']}}</td>
                        <td class='ShowGroupName'>{{$withdraw['amount']}}</td>
                        <td class='{{$withdraw['status'] == 1 ? 'payment' : 'ShowGroupName'}}'>{{$withdraw['status'] == 0 ? 'В работе' : 'Выплачено'}}</td>
                        <td><a href="{{route('withdrawSuccess', $withdraw['id'])}}"><button class="btn btn-outline-secondary" onclick="return confirm('Вы уверены?')">Выплачено</button></a></td>
                      </tr>

                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif



<div class="tab_admin">
    <div class="title">Поиск по id</div><br>
        <div class="content">
            <form method="post" action="/admin/getbyid">
            {{csrf_field()}}
            <div class="input-group mb-3">
                <input type="text" name="id" class="form-control" placeholder="ID" value="{{old('id')}}">
                <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Найти</button>
            </div>
            </form>
<br>
@if (isset($user))

        <a href="http://vk.com/id{{$user['vk_id']}}" target="_blank" class="nodecoration">
            <img src="{{$user['photo']}}" width="50" class="circle"> {{$user['first_name']}} {{$user['last_name']}}
        </a>

        <form action='/admin/store' method='post'>
        {{csrf_field()}}
          <input type="hidden" name="vk_id" value="<?=$user['vk_id']?>">
          <table class='table'>
          <tbody>
          <tr>
              <td class='align-middle ShowGroupName'>
              Зарегистрирован:
              </td>
              <td class='align-middle ShowGroupName'>
              {{ is_numeric($user['reg']) ? date('d.m.Y', $user['reg']) : date('d.m.Y', strtotime($user['last_seen'])) }}
              </td>
            </tr>
            <tr>
              <td class='align-middle ShowGroupName'>
                Заходил:
              </td>
              <td class='align-middle ShowGroupName'>
                {{ is_numeric($user['last_seen']) ? date('d.m.Y', $user['last_seen']) : date('d.m.Y', strtotime($user['last_seen'])) }}
              </td>
            </tr>
            <tr>
              <td class='align-middle ShowGroupName'>Пакет:</td>
              <td class='align-middle ShowGroupName'>
              <input type='text' maxlength='42' size='10' name='package' class='textbox margin' value='<?=$user['package']?>' aria-describedby='button-addon2' />
              </td>
            </tr>
            <tr>
              <td class='align-middle ShowGroupName'>
                Доступ до:<br>
                <?php if ($user['access'] < time()) echo '<label class=waiting>(неактивен)</label>'; else echo '<label class=payment>(активен)</label>'; ?>
              </td>
              <td class='align-middle ShowGroupName'>
              <input type="date" class="form-control" size='12' id="time_min" name="access" value="<?=date('Y-m-d', $user['access'])?>" >
            </tr>
            <tr>
              <td class='align-middle'>
                Реферал:
              </td>
              <td class='align-middle'>
                <input type='text' maxlength='42' size='12' name='ref' class='textbox margin' value='<?=$user['ref']?>' aria-describedby='button-addon2' />
                @if(isset($ref))
                  <a href="http://vk.com/id{{$ref['vk_id']}}" class="nodecoration" target="_blank"><img src="{{$ref['photo']}}" width="35" class="circle"> {{$ref['first_name']}}  {{$ref['last_name']}}</a>
                @endif
                </tr>
            <tr>
              <td class='align-middle'>
                Баланс:
              </td>
              <td class='align-middle'>
                <input type='text' maxlength='42' size='3' name='balance' class='textbox margin' value='<?=$user['balance']?>' aria-describedby='button-addon2' /> руб.
            </tr>
            <tr>
              <td></td>
              <td>
                <input class='btn btn-success btn_size' type='submit' id='btnMenu' value=' Сохранить ' onclick="return confirm('Вы уверены?')">
              </td>
            </tr>
          </tbody>
          </table>
        </form>
@endif
</div>
</div>



<div class="tab_admin">
    <div class="title">Сегодня онлайн: {{count($last_users) - 1}}</div><br>
    <div class="content">
    <div class="flex-start">
    @if(isset($last_users))
        @foreach($last_users as $last_user)
                @php
                    if ($last_user['vk_id'] == '573204714')
                    continue;
                @endphp
            <div class="new-users action">
            <a href="http://vk.com/id{{$last_user['vk_id']}}" target="_blank">
                <img src="{{$last_user['photo']}}" width="50" class="circle"> <br>


                @if($last_user['access'] > time())
                    <span style="color: green;">{{$last_user['first_name']}}<br> {{$last_user['last_name']}}</span>
                @else
                    {{$last_user['first_name']}}<br> {{$last_user['last_name']}}
                @endif
            </a>{{ $last_user['vk_id'] }}
            <?php

                $utm = \App\utm::where('vk_id', '=', $last_user['vk_id'])->first();
                if ($utm !== null)
                {
                    $utm = $utm->toArray();
                    $utm = array_diff($utm, [null]);
                    unset($utm['vk_id']);
                    unset($utm['id']);
                    unset($utm['created_at']);
                    unset($utm['updated_at']);

                    $utm = implode(",", $utm);
                    echo '<br>'.$utm;
                }

            ?>
            </div>
        @endforeach
    @endif
            </div>
</div>
</div>

<!--
<div class="tab_admin">
    <div class="title">Новых регистраций: {{count($new_users)}}</div><br>
    <div class="content">
    <section class="flex-start">
    @if(isset($new_users))
        @foreach($new_users as $new_user)
            <div class="new-users action">
            <a href="http://vk.com/id{{$new_user['vk_id']}}" target="_blank">
                <img src="{{$new_user['photo']}}" width="50" class="circle"> <br>
                @if($new_user['access'] > time())
                    <span style="color: green;">{{$new_user['first_name']}}<br> {{$new_user['last_name']}}</span>
                @else
                    {{$new_user['first_name']}}<br> {{$new_user['last_name']}}
                @endif
            </a>{{ $new_user['vk_id']}}
            </div>
        @endforeach
    @endif
    </section>
</div>
</div>-->



<div class="tab_admin">
    @include('admin.newPayment')
</div>

<div class="tab">
<div class="title">Выберите период</div>
    <div class="content">
<form action="/admin/getpayments" method="post">
@csrf
    <label class='label margin'><b>За какой период смотрим оплаты?</b></label>
    <div class='row'>
        <div class='col-md-3'>
            <input type="date" size="3" id="time_min"  class="form-control" name="time_min" value="{{isset($time_min) ? $time_min : null }}">
        </div>
        <div class='col-md-3'>
            <input type="date" size="3"  id="time_max"  class="form-control" name="time_max" value="{{isset($time_max) ? $time_max : null }}">
        </div>
        <div class='col-md-3'>
            <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Поиск "  onclick="change()">
        </div>
    </div>



</form>
</div>
</div>

@if(count($payments) > 0)
<div class="tab">
<div class="title"><small>Оплаты: <b>{{count($payments)}}</b> Заработано: <b>{{$summ}}</b> руб.</small> Реферальные: {{ $refka }}</div>
    <div class="content">
        <table class='table'>
            <thead>
                <tr>
                    <th class='ShowGroupName' colspan='2'>Профиль</th>
                    <th class='ShowGroupName'>VK ID</th>
                    <th class='ShowGroupName'>Пакет</th>
                    <th class='ShowGroupName'>Сумма</th>
                    <th class='ShowGroupName'>N</th>
                    <th class='ShowGroupName'>Дата</th>
                    <th class='ShowGroupName'>Reg</th>
                    <th class='ShowGroupName'>UTM</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <?php
                if($payment['utm'] !== null ){

                    $utm = $payment['utm'];
                    $utm = array_diff($utm, [null]);
                    unset($utm['vk_id']);
                    unset($utm['id']);
                    unset($utm['created_at']);
                    unset($utm['updated_at']);

                    $utm = implode(",", $utm);

                } else $utm = null;
                ?>
                    <tr>
                        <td width='5%'><img src="{{$payment['photo']}}" class="circle"> </td>
                        <td>
                            <a href="https://vk.com/id{{$payment['vk_id'] }}" class='ShowGroupName' target='_blank'>{{ $payment['first_name'] .' '. $payment['last_name']}}</a>
                            <br><?php if ($payment['ref'] !== null) echo "<a href=https://vk.com/id".$payment['ref']['vk_id']." class='ShowGroupName'><img src=".$payment['ref']['photo']." class=circle width=30 height=30> <small>" . $payment['ref']['first_name'].' '.$payment['ref']['last_name'].'</a></small>';?>
                        </td>
                        <td class='ShowGroupName'>{{$payment['vk_id']}}</td>
                        <td class='ShowGroupName'>{{$payment['package']}}</td>
                        <td class='ShowGroupName'>{{$payment['amount']}}</td>
                        <td class='ShowGroupName'>{{$payment['count']}}</td>
                        <td class='ShowGroupName'>{{date("d.m.Y", strtotime($payment['date']))}}</td>
                        <td class='ShowGroupName'><small>{{ (is_numeric($payment['reg'])) ? date("d.m.Y", $payment['reg']) : $payment['reg'] }}</small></td>
                        <td class='ShowGroupName'><small>{{$utm}}</small></td>
                    </tr>

                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif


<div class="tab">

<div class="title">Новые регистрации</div>
<div class="between">
    @if(isset($users))
    @foreach($users as $user)
        <a href="https://vk.com/id{{$user['vk_id']}}" target="_blank" class="new-users ShowGroupName">
            <img src="{{$user['photo']}}" class="circle"> <br>{{$user['first_name']}}<br> {{$user['last_name']}}
        </a>
    @endforeach
    @endif
</div>
<div>
        @if(isset($users))
        {{ $users->onEachSide(0)->links() }}
        @endif
        </div>
</div>

<!--
<div class="tab">
<div class="title">Видео-обзор</div><br>
<iframe width="840" height="472" src="https://www.youtube.com/embed/FD6uStLFVWc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>
-->

</div>


</div>

@endsection
