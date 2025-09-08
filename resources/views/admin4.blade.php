@extends('admin-layout')

@section('content')

<div class="tab">
<div class="title">Статистика доходов по месяцам</div>
    <div class="content">

@if(isset($partners))
<div class="tab">
<div class="title">Партнеры с доходами </div>
    <div class="content">
        <table class='table'>
            <thead>
                <tr>
                    <th class='ShowGroupName'>Профиль</th>
                    <th class='ShowGroupName'>Заработал</th>
                    <th class='ShowGroupName'>Был online</th>
                </tr>
            </thead>
            <tbody>
                @php $summ = 0; @endphp
                @foreach($partners as $partner)
                    <tr>
                        <td class='ShowGroupName'>
                            <a href="http://vk.ru/id{{$partner['vk_id']}}" target="_blank" class="nodecoration">
                                <img src="{{$partner['photo']}}" width="50" class="circle"> {{$partner['first_name']}} {{$partner['last_name']}}
                            </a>
                        </td>
                        <td class='ShowGroupName'>{{$partner['balance']}}</td>
                        <td class='ShowGroupName'>{{ is_numeric($partner['last_seen']) ? date('d.m.Y', $partner['last_seen']) : date('d.m.Y', strtotime($partner['last_seen'])) }}</td>
                    </tr>
                @php $summ += $partner['balance']; @endphp
                @endforeach
            </tbody>
        </table>
    </div>
</div> <hr>
<h3>
Мы им должны: {{ $summ }}
</h3></hr>
@else
    Заработано партнерами: {{ $refka }} <a href="{{ route('getpartners') }}">Показать партнеров</a><hr>
@endif


        @foreach($stats as $key => $value)
            {{ $key }} - {{ $value }} руб.<br>
        @endforeach



</div></div></div>

@endsection
