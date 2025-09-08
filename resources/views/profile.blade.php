@extends('layout')

@section('title') @parent Мои задачи @endsection

@section('content')
<div class="tab">
<div class="title">Мои задачи</div>
<div class="content">
Для обновления данных обновите страницу<br><br>

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')


        <table class='table'>
            <thead>
                <tr>
                    <th></th>
                    <th class='ShowGroupName'>Имя</th>
                    <th class='ShowGroupName'>% выполнения</th>
                    <th class='ShowGroupName'>Создано</th>
                    <th width="10%"></th>
                    <th width="10%"></th>
                </tr>
            </thead>
            <tbody>
                @php
                @endphp

                @forelse($data as $item)
                      <tr>
                        <td width='1%'><img src="https://zebra-target.ru/PNG/{{ ($item->status) ? 'icon_check.png' : 'icon_hourglass.png' }}" width="16" height="16"></td>
                        <td class='ShowGroupName'><?php if (strlen($item->name) > 60) { echo mb_strcut($item->name, 0, 96). '...'; } else echo $item->name; ?></td>
                        <td class='ShowGroupDesc' width="10%"><div id='response'><?php echo ($item['percent'] == 100) ? '<span class=workPercent>100%</span>' : '<b>'.$item['percent'].'%</b>'; ?></div></td>
                        <td class='ShowGroupDesc' width="10%">{{ date("d.m.Y", strtotime($item->updated_at)) }}</td>
                        <td>
                            @if($item['type'] == 'liders' and $item['status'] == 1)
                                <a href="{{route('getworkliders', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'ugroups' and $item['status'] == 1)
                                <a href="{{route('getworkusersgroups', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'usersfilter' and $item['status'] == 1)
                                <a href="{{route('getworkusersfilter', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'usersgroups' and $item['status'] == 1)
                                <a href="{{route('getworkusersgroups', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'topfollowers' and $item['status'] == 1)
                                <a href="{{route('getworktopfollowers', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'getmembers' and $item['status'] == 1)
                                <a href="{{route('getworkgetmembers', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'filter' and $item['status'] == 1)
                                <a href="{{route('getworkfilterusers', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['type'] == 'getallmembers' and $item['status'] == 1)
                                <a href="{{route('getworkallmembers', $item['id'])}}" class="btn btn-success btn-sm">Открыть</a>
                            @elseif($item['status'] !== 9)
                                <a href="{{route('killmywork', $item['id'])}}" class="btn btn-warning btn-sm" onclick="return confirm('Вы уверены?')">Стоп</a>
                            @endif
                        </td>
                        <td>
                        @if($item['status'] == 1 or $item['status'] == 9)
                            <form method="post" action="{{route('workdelete', $item['id'])}}">
                                @method('post')
                                @csrf
                                <input type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')" value="Удалить">
                            </form>
                        @endif
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
     
    </div>
</div> 

@endsection