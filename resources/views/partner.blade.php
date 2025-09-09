@extends('layout')

@section('title') @parent Партнерская программа @endsection


@section('content')
<div class="tab">

<div class="title">Партнерская программа </div>
    <div class="content">

    @include('errors.session')

        Рекомендуйте наш сервис другим людям и получайте 30% от каждого их платежа!
        @if(session('token'))
        <br><label class='label'><b>Ваша партнерская ссылка:</b></label><br>
            <div class="input-group">
            <input type="text" id='textarea' readonly class="form-control" value="http://zebra-target.ru/?r={{session('id')}}">
            <span class="input-group-btn">
                <button class="btn btn-outline-dark" type="button"  onclick='copy()'> Скопировать ссылку </button>
            </span>
            </div>
        @endif
<br>
        <label class='label'><b>Как это работает?</b></label>
        <p class="size13">
        Достаточно просто дать человеку вашу партнерскую ссылку, и если в течение месяца он авторизуется на сайте, он сразу же будет засчитан как приглашенный вами.
        Как только он оплатит полный доступ к сервису, вам сразу же будет начислено партнерское вознаграждение, которое составляет 30% от суммы платежа.
        </p>
        @if(isset($ref))
        <label class='label'><b>Ваш пригласитель:</b></label><br>
            <img src="{{$ref['photo']}}" width="30" class="circle">
            <a href="http://vk.com/id{{$ref['vk_id']}}" class='ShowGroupName' target="_blank">{{$ref['first_name']}} {{$ref['last_name']}}</a>

        @endif
    </div>
</div>

<div class="tab">
    <div class="content">

    <h5>Баланс: {{ $user->balance }} руб.</h5><br>
            <a href="{{route('withdraw')}}"><button class="btn btn-outline-secondary">Запросить вывод</button></a>
            <a href="{{route('pay-access')}}"><button class="btn btn-outline-secondary">Оплатить полный доступ</button></a>
    </div>
</div>

<div class="tab">
<div class="title">Моя структура: <b>{{count($referals)}}</b></div>
    <div class="content">
        <table class='table'>
            <thead>
                <tr>
                    <th colspan='2' class='ShowGroupName'>Профиль</th>
                    <th class='ShowGroupName'>Дата регистрации</th>
                    <th class='ShowGroupName'>Количество оплат</th>
                    <th class='ShowGroupName'>Был online</th>

                </tr>
            </thead>
            <tbody>
            @if(isset($referals))
                @foreach($referals as $ref)

                    <tr>
                        <td width="30"><img src="{{$ref['photo']}}" width="30" class="circle"> </td>
                        <td><a href="http://vk.com/id{{$ref['vk_id']}}" class='ShowGroupName' target="_blank">{{$ref['first_name']}} {{$ref['last_name']}}</a></td>
                        <td class='ShowGroupDesc'>{{ is_numeric($ref['reg']) ? date('d.m.Y', $ref['reg']) : date('d.m.Y', strtotime($ref['reg'])) }}</td>
                        <td class="payment">{{isset($userPayments[$ref['vk_id']]) ? $userPayments[$ref['vk_id']] : ''}}</td>
                        <td class='ShowGroupDesc'>{{ is_numeric($ref['last_seen']) ? date('d.m.Y', $ref['last_seen']) :  date('d.m.Y', strtotime($ref['last_seen'])) }}</td>
                    </tr>

                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>


<div class="tab">
<div class="title">Оплаты в структуре: <b>{{count($payments)}}</b></div>
    <div class="content">
        <table class='table'>
            <thead>
                <tr>
                    <th colspan='2' class='ShowGroupName'>Профиль</th>
                    <th class='ShowGroupName'>Пакет</th>
                    <th class='ShowGroupName'>Начислено</th>
                    <th class='ShowGroupName'>Дата</th>
                </tr>
            </thead>
            <tbody>
            @if(isset($payments))
                @foreach($payments as $payment)
                    <tr>
                        <td width="30"><img src="{{$payment['photo']}}" width="30" class="circle"> </td>
                        <td class='ShowGroupName'><a href="http://vk.com/id{{$payment['user_id']}}" class='ShowGroupName' target="_blank">{{$payment['first_name'] . ' ' . $payment['last_name']}}</a></td>
                        <td class='ShowGroupName'>{{$payment['package']}}</td>
                        <td class='payment'>+ {{$payment['reward']}} руб.</td>
                        <td class='ShowGroupDesc'>{{date("d.m.Y", strtotime($payment['created_at']))}}</td>
                    </tr>

                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>


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
                    <th class='ShowGroupName'>Дата</th>
                </tr>
            </thead>
            <tbody>
                @foreach($withdraws as $withdraw)
                    <tr>
                        <td class='ShowGroupName'>{{$withdraw['method'].' '.$withdraw['req']}}</td>
                        <td class='ShowGroupName'>{{$withdraw['amount']}}</td>
                        <td class='{{$withdraw['status'] == 1 ? 'payment' : 'ShowGroupName'}}'>{{$withdraw['status'] == 0 ? 'В работе' : 'Выплачено'}}</td>
                        <td class='ShowGroupDesc'>{{date("d.m.Y", strtotime($withdraw['created_at']))}}</td>
                    </tr>

                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>


@endsection
