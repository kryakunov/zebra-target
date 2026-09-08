@extends('layout')

@section('title') @parent Отслеживание новых друзей @endsection

@section('content')
<div class="tab">
<div class="title">Отслеживание новых друзей у пользователей</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

    <div class="for-mobile">
        <a class="btn btn-outline-success btn-sm" style="margin-bottom: 10px; width: 100%" href="{{route('NewFriendsCreate')}}" onlick="ym(61877542,'reachGoal','click', {URL: document.location.href}); return true;)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
            </svg>
            Добавить пользователей</a>
        @if(session('access') !== null)<a class="btn btn-success btn-sm" style="margin-bottom: 10px; width: 100%"  href="{{route('NewFriendsUpdate')}}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
            </svg>
            Поиск новых друзей</a>@endif

    </div>

    <div class="for-desktop">
        <a class="btn btn-outline-success btn-sm" href="{{route('NewFriendsCreate')}}" onlick="ym(61877542,'reachGoal','click', {URL: document.location.href}); return true;)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
        <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
        </svg>
        Добавить пользователей</a>
        @if(session('access') !== null)<a class="btn btn-success btn-sm" href="{{route('NewFriendsUpdate')}}">Поиск новых друзей</a>@endif
        <br><br>
    </div>

    <div class="for-desktop">
        <table class='table'>
            <thead>
                <tr>
                    <th colspan="2" class='ShowGroupName'>Профиль</th>
                    <th class='ShowGroupName'>Новых друзей</th>
                    <th class='ShowGroupName'>Удаленных</th>
                    <th class='ShowGroupName'>Добавлено</th>
                    <th class='ShowGroupName'>Обновлено</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                      <tr>
                        <td width="30"><img src="{{$user['photo']}}" width="30" class="circle"> </td>
                        <td><a href="http://vk.ru/id{{$user['id']}}"  class='ShowGroupName' target="_blank">{{$user['first_name']}} {{$user['last_name']}}</a></td>
                        <td class='payment'><a href="{{route('NewFriendsGet', ['id' => $user['id']])}}">{{($user['new_friends'] > 0) ? $user['new_friends'] : ''}}</a></td>
                        <td class='payment'><a href="{{route('DelFriendsGet', ['id' => $user['id']])}}">{{($user['delete_friends'] > 0) ? $user['delete_friends'] : ''}}</a></td>
                        <td class='ShowGroupDesc'>{{date("d.m", strtotime($user['created_at']))}}</td>
                        <td class='ShowGroupDesc'>{{date("d.m", strtotime($user['updated_at']))}}</td>
                        <td>
                            <form method="post" action="{{ route('NewFriendsDelete', $user['track_id'])}}">
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


    <div class="for-mobile">
    @forelse($users as $user)
        <div style="
            margin-bottom: 10px;
            border-radius: 8px; /* Rounding the corners */
            background-color: #f0f0f0; /* Background color */
            padding: 16px; /* Padding */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Optional: shadow for depth */
        ">
    <div class="d-flex flex-row">
        <div class="p-1"><img src="{{$user['photo']}}" width="60" class="circle"></div>
        <div class="p-2">
            <a href="http://vk.ru/id{{$user['id']}}"  class='ShowGroupName' target="_blank">{{$user['first_name']}} {{$user['last_name']}}</a>
            <div class="ShowGroupDesc"  style="margin-top: 10px">Новых друзей: <a  class="new-friends" href="{{route('NewFriendsGet', ['id' => $user['id']])}}">{{($user['new_friends'] > 0) ? $user['new_friends'] : '0'}}</a></div>
            <div class="ShowGroupDesc">Удаленных: <a class="new-friends"  href="{{route('DelFriendsGet', ['id' => $user['id']])}}">{{($user['delete_friends'] > 0) ? $user['delete_friends'] : '0'}}</a></div>

        </div>
    </div>

    <div class="d-flex flex-row ShowGroupDesc  justify-content-around mt-4">
        Обновлено  {{date("d.m", strtotime($user['updated_at']))}}
        <form method="post" action="{{ route('NewFriendsDelete', $user['track_id'])}}">
            @method('delete')
            @csrf
            <input type="submit" class="btn-reset ShowGroupDesc" onclick="return confirm('Вы уверены?')" value="Удалить">
        </form>
    </div>
        </div>
    @empty
    @endforelse

    </div>
</div>

@endsection
