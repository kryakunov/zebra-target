@extends('layout')

@section('content')
<div class="tab">
<div class="title">Отслеживание новых друзей у пользователей</div>
<div class="content">

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')
 

    @if(isset($_GET['id']))
        <a href="{{route('myworks')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a>
    @else
        <a href="{{route('NewFriendsShow')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a>
    @endif
    
    <br><br>
 
    <input type="hidden" name="parentId" value="{{ (isset($request['parent_id'])) ? $request['parent_id'] : '' }}">
    <form action="{{route('NewFriendsStore')}}" method="post" class="mb-4">
        @csrf

        <input type="hidden" name="parentId" value="{{ (isset($_GET['id'])) ? $_GET['id'] : '' }}">
        <div class="form-group">       

            <label class='label'>Вставьте список ID пользователей:</label>
            <textarea class="output-panel form-control mb-3"  name="users" rows="8" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users']) ? $request['users'] : old('users') }}</textarea>
        </div>
        <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Добавить в отслеживание"  onclick="change()">
    </form> 


</div>
</div>
@endsection