@extends('layout')

@section('content')
<div class="tab">
<div class="title">Добавить группы в отслеживание</div>
<div class="content">

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')
 

    @if(isset($_GET['id']))
        <a href="{{route('myworks')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a>
    @else
        <a href="{{route('NewMembersShow')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a>
    @endif

     <br><br>
    <form action="{{route('NewMembersStore')}}" method="post" class="mb-4">
        @csrf

        <input type="hidden" name="parentId" value="{{ (isset($_GET['id'])) ? $_GET['id'] : '' }}">
        <div class="form-group">       
            <textarea class="output-panel form-control mb-3"  name="groups" rows="8" placeholder="Вставьте группы по одному ID на строку">{{isset($request['groups']) ? $request['groups'] : old('groups') }}</textarea>
        </div>
        <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Добавить в отслеживание"  onclick="change()">
    </form> 


</div>
</div>
@endsection