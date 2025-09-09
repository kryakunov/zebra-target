@extends('layout')

@section('title') @parent Мои задачи @endsection

@section('content')
<div class="tab">
<div class="title">Мои задачи - Сообщества пользователей</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')


<label class='label'><b>{{ $name }} - Найдено {{ count($data) }} сообществ</b></label> <br><br>

<a href="{{route('profile')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a>

<button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
    Получить ID всех групп
  </button>

  <button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample">
    Исходные данные
  </button>
<br><br>


  <div class="collapse" id="collapseExample">
    <label class='label'><b>Найдено {{ count($data) }} сообществ</b></label>
    <textarea class="output-panel form-control mb-2"  id="textarea" rows="9">@forelse($data as $value){{$value['id']}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
</div>

<div class="collapse" id="collapseExample2">
    <label class='label'><b>В исходном списке {{ count($sourceData) }} элементов</b></label>
    <textarea class="output-panel form-control mb-2"  id="textarea2" rows="9">@forelse($sourceData as $value){{$value}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy2()">Скопировать</button> <br><br>
</div>


        <table class='table'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th colspan='2'>Сообщество</th>
                    <th>Участников</th>
                    <th>Совпавших</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $key => $value)
                <tr>
                    <td class='ShowGroupDesc'>{{ $value['id'] }}</td>
                    <td width='5%'><img src={{$value['photo_50']}} class='circle' width=35 height=35></td>
                    <td ><a class='ShowGroupName' href=http://vk.com/club{{$value['id']}} target='_blank'> {{ $value['name'] }}</a><br><small>{{(isset($value['status'])) ? $value['status'] : ''}}</small></td>
                    <td class='ShowGroupDesc'> {{ (isset($value['members_count'])) ? $value['members_count'] : '' }}</td>
                    <td class='ShowGroupDesc'> {{ $count[$value['id']] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

@endsection



