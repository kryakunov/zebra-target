@extends('layout')

@section('title') @parent Мои задачи @endsection

@section('content')
<div class="tab">
<div class="title">Мои задачи - Лидеры мнений</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')


<label class='label'><b>{{ $name }} - Найдено {{ count($data) }} профилей</b></label> <br><br>


<a href="{{route('profile')}}" class="btn btn-outline-dark btn-sm" ><< Назад</a>

<button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
    Получить ID всех пользователей
</button>

<button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample">
    Исходные данные
</button>
<br><br>

<div class="collapse" id="collapseExample">
    <label class='label'><b>Найдено {{ count($users) }} профилей</b></label>
    <textarea class="output-panel form-control mb-2"  id="textarea" rows="9">@forelse($users as $value){{$value['id']}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
</div>


<div class="collapse" id="collapseExample2">
    <label class='label'><b>В исходном списке {{ count($sourceData) }} элементов</b></label>
    <textarea class="output-panel form-control mb-2"  id="textarea2" rows="9">@forelse($sourceData as $value){{$value}}
@empty<p>Ничего не найдено</p>@endforelse</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy2()">Скопировать</button> <br>
</div><br>

        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th class='ShowGroupName'>ID</th>
                    <th class='ShowGroupName' colspan='2'>Профиль</th>
                    <th class='ShowGroupName'>Пересечений</th>
                    <th class='ShowGroupName'>%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $value)
                @php
                    $percent = round(($data[$value['id']] / $count) * 100);
                @endphp
                      <tr>
                        <td class='ShowGroupDesc' width="5%">{{$value['id'] }}</small></td>
                        <td width='25' class='align-middle'><img src={{ $value['photo_50'] }} class='circle' width=35 height=35></td>
                        <td><a class='align-middle ShowGroupName' href='https://vk.ru/id{{ $value['id'] }}' target='_blank'>{{$value['first_name']}} {{$value['last_name']}}</a><br><small><?php if(isset($value['status'])) echo $value['status'];?></small>
                        <td class='ShowGroupDesc' >{{ $data[$value['id']] }}</td>
                        <td class='ShowGroupDesc' >
                            {{ $percent }}%
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@endsection



