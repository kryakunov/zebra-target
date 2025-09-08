@extends('admin-layout')

@section('content')

<div class="tab">
<div class="title">Выберите период</div>
    <div class="content">
<form action="{{ route('getpaymentusers') }}" method="post">
@csrf
    <label class='label margin'><b>За какой период смотрим оплаты?</b></label>
    <div class='row'>
        <div class='col-md-3'>
            <input type="date" size="3" id="time_min"  class="form-control" name="time_min" value="{{isset($request['time_min']) ? $request['time_min'] : null }}">
        </div>
        <div class='col-md-3'>
            <input type="date" size="3"  id="time_max"  class="form-control" name="time_max" value="{{isset($request['time_max']) ? $request['time_max'] : null }}">
        </div>
        <div class='col-md-3'>
            <input class="btn btn-success btn_size" type="submit"  id="btnMenu" value=" Поиск "  onclick="change()">
        </div>
    </div> 
</form> 
<br>

@if(isset($users) && count($users) > 0)
Найдено: <b>{{ count($users) }}</b>
<div class="row">
    <div class="col-md-6">
        <form action="{{ route('storepaymentusers') }}" method="post">
        @csrf
            <textarea class="output-panel form-control @error('groups') is-invalid @enderror" id="textarea" rows="18" name="users" rows="5" ><?php foreach($users as $user)
            echo $user . "\n";
            ?></textarea><br>

    </div>
    <div class="col-md-6">

        <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
        <input type="hidden" name="count" value="{{ count($users) }}">
        <input type="submit" class="btn btn-outline-success margin" value="Сохранить в список">
        </form>

    </div>
</div>
@else
Не найдено
@endif

</div>

@endsection