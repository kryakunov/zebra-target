@extends('layout')

@section('content')
<div class="tab">
@if(session('token'))
<h1 class="title">Преобразовать ID в посты</h1>
@endif
<div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')


<form action="{{route('tool1Post')}}" method="post" class="mb-4">
@csrf

@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте список постов:</label>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('posts') is-invalid @enderror"  name="posts" rows="5" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['posts']) ? $request['posts'] : null }}</textarea>
    @error('posts')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<!--
    <label class='label'><b>Результаты в формате:</b></label><br>
    <div class="result_format">
    <label class="cursor-pointer"><input class="radio" type="radio"  name="format" value="0" <?php if(!isset($request['format']) or $request['format'] == '0') echo 'checked'; ?>> Только аватарки</label><br>
    <label class="cursor-pointer"><input class="radio" type="radio"  name="format" value="1" <?php if(isset($request['format']) and $request['format'] == '1') echo 'checked'; ?>> Подробный профиль</label><br>
    </div>-->

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Показать посты"  onclick="change()">
</form>
@endif

@include('errors.exceptions')
@include('errors.session')



@if(isset($data))
    <div class="alert alert-success">
        Показано: <b>{{count($data)}}</b> постов
    </div>

<?php
$cc = 0;
?>



    @foreach($data as $value)

        <div class="mywork">
            <span class="ShowGroupName">
            <?php
            $photo = '';
            $name = '';

                if($value['from_id'] > 0) {
                    $photo = $profiles[$value['from_id']]['photo'];
                    $name = $profiles[$value['from_id']]['name'];
                } else {
                    if (!isset($groups[$value['from_id']])) {
                        $photo = '';
                        $name = '';
                    } else {
                        $photo =  $groups[$value['from_id']]['photo'];
                        $name = $groups[$value['from_id']]['name'];
                    }
                }
            ?>

            <img src={{($photo) ? $photo : '' }} class=circle width=35 height=35> {{ ($name) ? $name : '' }}
            </span>
            <br><br>
            <div class="result_format time">{{ $value['text'] }}</div>
            <br>

            <div class="time">
                <img src="https://zebra-target.ru/PNG/icon-like.png" width="16" height="16"> {{ $value['likes'] }} &nbsp;
                <img src="https://zebra-target.ru/PNG/icon-comment.png" width="16" height="16"> {{ $value['comments'] }} &nbsp;
                <img src="https://zebra-target.ru/PNG/icon-repost.png" width="16" height="16"> {{ $value['reposts'] }} &nbsp;
                <img src="https://zebra-target.ru/PNG/icon_clock_alt.png" width="16" height="16"> {{ date('d.m.Y H:i', $value['date']) }} &nbsp;
                <a href="https://vk.ru/wall{{$value['post']}}" target="_blank">Ссылка на пост</a>
            </div>
        </div>
    @endforeach


@endif



@include('partials.tool-guest-end')
@endsection
