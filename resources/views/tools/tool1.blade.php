@extends('layout')

@section('content')
<div class="tab">
@if(session('token'))
<h1 class="title">Преобразовать ID в профили</h1>
@endif
<div class="content">
@include('_ScriptDesk')
@include('partials.tool-guest-start')


<form action="{{route('tool1Post')}}" method="post" class="mb-4">
@csrf

@if(isset($work))
	@include('_work')
@else

<label class='label'>Вставьте список пользователей:</label>
<div class="form-group mb-3">
    <textarea class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="5" placeholder="Вставьте пользователей по одному ID на строку">{{isset($request['users']) ? $request['users'] : null }}</textarea>
    @error('users')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


    <label class='label'><b>Результаты в формате:</b></label><br>
    <div class="result_format">
    <label class="cursor-pointer"><input class="radio" type="radio"  name="format" value="0" <?php if(!isset($request['format']) or $request['format'] == '0') echo 'checked'; ?>> Только аватарки</label><br>
    <label class="cursor-pointer"><input class="radio" type="radio"  name="format" value="1" <?php if(isset($request['format']) and $request['format'] == '1') echo 'checked'; ?>> Подробный профиль</label><br>
    </div>

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Показать профили"  onclick="change()">
</form>
@endif

@include('errors.exceptions')
@include('errors.session')



@if(isset($data))
    <div class="alert alert-success">
        Показано: <b>{{count($data)}}</b> профилей
    </div>

    @if($request['format'] == 0)
	@foreach($data as $value)
        <a href=https://vk.ru/id{{$value['id']}} target=_blank><img src={{$value['photo_50']}} class=circle ></a>
	@endforeach
    @elseif($request['format'] == 1)
    <table class='table'>
        <thead>
            <tr>
                <th colspan='2' class='ShowGroupName' width='50%'>Профиль</th>
                <th class='ShowGroupName'>Подписчиков</th>
                <th class='ShowGroupName'>Профиль</th>
                <th class='ShowGroupName'>Действие</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $value)
            <tr>
                <td width='10'><img src="{{$value['photo_50']}}" class='circle' width='50' height='50'></td>
                <td>
                    <a href=https://vk.ru/id{{$value['id']}} target=_blank class='ShowGroupName'>
                        {{$value['first_name'].' '.$value['last_name']}}
                    </a><br>
                    <label class='ProfileStatus'>
                        {{isset($value['status'])?$value['status']:''}}
                    </label>
                </td>
                <td class='ShowGroupDesc'>{{isset($value['followers_count']) ? $value['followers_count'] : ''}}</td>
                <td class='ShowGroupDesc'><?php if(isset($value['is_closed']) and $value['is_closed'] == true)echo'Закрытый';else echo'Открытый';?></td>
                <td><a href=https://vk.ru/id{{$value['id']}} target=_blank class='btn btn-outline-success btn-sm' id='btn-views'>Перейти</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @endif

@endif



@include('partials.tool-guest-end')
@endsection
