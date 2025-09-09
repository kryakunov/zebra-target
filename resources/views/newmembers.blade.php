@extends('layout')

@section('title') @parent Отслеживание новых вступлений в группы @endsection
@section('content')
<div class="tab">
<div class="title">Отслеживание новых вступлений в группы</div>
<div class="content">
@include('_ScriptDesk')


@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

@if(session('group'))
<?php $group = session('group'); ?>
<div class="alert alert-success">
       В сообществе <img src="{{$group['photo']}}" width="30" class="circle"> <b>{{$group['name']}}</b> найдено <b>{{$group['newmembers']}}</b> новых участников
    </div>
@endif

        <a class="btn btn-success btn-sm" href="{{route('NewMembersCreate')}}">+ Добавить группы</a>
        <br><br>

        <table class='table'>
            <thead>
                <tr>
                    <th colspan="3" class='ShowGroupName'>Сообщество</th>
                    <th class='ShowGroupName'>Новых участников</th>
                    <th class='ShowGroupName'>Добавлено</th>
                    <th class='ShowGroupName'>Обновлено</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; ?>
                @forelse($groups as $group)
                      <tr>
                      <td class='ShowGroupDesc'>{{ ++$i }}</td>
                        <td width="30"><img src="{{$group['photo_50']}}" width="30" class="circle"> </td>
                        <td><a href="http://vk.com/club{{$group['id']}}"  class='ShowGroupName' target="_blank">{{$group['name']}} </a></td>
                        <td class='payment'><a href="{{route('NewMembersGet', ['id' => $group['id']])}}">{{($group['new_members'] > 0) ? $group['new_members'] : ''}}</a></td>
                        <td class='ShowGroupDesc'>{{date("d.m", strtotime($group['created_at']))}}</td>
                        <td class='ShowGroupDesc'>{{date("d.m", strtotime($group['updated_at']))}}</td>
                        <td class='ShowGroupDesc'><a class="btn btn-outline-success btn-sm" href="{{route('NewMembersUpdate', ['id' => $group['id']])}}">Поиск</a></td>
                        <td>
                            <form method="post" action="{{ route('NewMembersDelete', $group['track_id'])}}">
                                @method('delete')
                                @csrf
                                <input type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Вы уверены?')" value="Удалить">
                            </form>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
