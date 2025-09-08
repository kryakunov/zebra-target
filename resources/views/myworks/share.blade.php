@extends('layout')

@section('title') @parent Менеджер задач @endsection

@section('content')
<div class="tab">
<div class="title">Менеджер задач</div>
<div class="content">


@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

@if(session('chain'))
    <br><span class='chainshow'>{{ session('chain') }} </span><br><br>
@endif

@forelse($data as $item)

<div class="mywork">
    <div class="row">
    <div class="col-md-5">
        <div class="myworkheader">
            @if($item['status'] == 1)
                  {{ $item->name }}
            @endif

            @if($item->share_parent == 1 && $item->parent_id !== null)
                <br><a href="{{route('getchainshare', ['id' => $item->id])}}" class="chain">Показать цепочку</a>
            @endif
        </div>
        <div class="myworkabout">
            <div class="myworkname"><img src="https://zebra-target.ru/PNG/icon_cloud_alt.png" width="16" height="16"> {{ $item->WorkType->type_name }}</div>
            <div class="myworkname">
                <img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="16" height="16">
                {{ ($item->count) ? $item->count : '0' }}
                {{ ($item->WorkType->type_desc == 'users') ? 'пользователей' : '' }}
                {{ ($item->WorkType->type_desc == 'groups') ? 'сообществ' : '' }}
                {{ ($item->WorkType->type_desc == 'socialnetworks') ? 'контактов' : '' }}
            </div>

        </div>
    </div>
    <div class="col-md-2 middle">
       @if($item['status'] == 1 or $item['status'] == 9)
            @include('myworks._use')
        @endif
    </div>
    <div class="col-md-2 middle">
        @if($item['status'] == 1 or $item['status'] == 9)
            @include('myworks._actions')
        @endif
    </div>

    <div class="col-md-3 middle right usershare align-right">

            <img src="{{ $item->user->photo }}" width="35" height="35" class="circle">
            <span class="right">
            <a href="https://vk.ru/id{{ $item->user->vk_id }}" target="_blank" class="submitbutton">
            &nbsp; &nbsp; {{ $item->user->first_name }} {{ $item->user->last_name }}
            </a><br>
        Автор шаблона</span>
    </div>
    </div>

</div>
@empty
@endforelse

    </div>
</div>

@endsection
