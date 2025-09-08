@extends('layout')

@section('title') @parent Облако @endsection

@section('content')
<div class="tab">
<div class="title">Мои базы пользователей</div>
<div class="content">
Здесь вы можете сохранять, обновлять и работать с базами пользователей.
<br><br>

@include('errors.validate')
@include('errors.session')
@include('errors.exceptions')

        <a class="btn btn-success btn-sm" href="{{route('cloud.create')}}">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-plus-fill" viewBox="0 0 16 16">
        <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m.5 4v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 1 0"/>
        </svg>
        Добавить базу </a>
        <a class="btn btn-outline-dark btn-sm" href="{{route('cloud.index')}}">Все</a>
        <a class="btn btn-outline-dark btn-sm" href="{{route('cloudshowusers')}}">Пользователи</a>
        <a  class="btn btn-outline-dark btn-sm" href="{{route('cloudshowgroups')}}">Сообщества</a>
        <a  class="btn btn-outline-dark btn-sm" href="{{route('cloudshowposts')}}">Посты</a>
        <!--<a class="btn btn-outline-dark btn-sm" href="{{route('getblacklist')}}">Черный список</a>-->
        <br>

<br>
        <table class='table'>
            <thead>
                <tr>
                    <th class='ShowGroupName'>Название</th>
                    <th width="10%"></th>
                    <th width="10%"></th>
                    <th width="10%"></th>
                    <th width="10%"></th>
                </tr>
            </thead>
            <tbody>
                @php $i = 0; @endphp
                @forelse($data as $item)
                      <tr>

                        <td class='ShowGroupDesc'>
                <span class="cloudWorkTitle">
                            <?php if (strlen($item->name) > 160) { echo substr($item->name, 0, 60). '...'; } else echo $item->name; ?>
</span>
   <div class="time">
                                   
                                   <img src="https://zebra-target.ru/PNG/icon_calendar.png" width="13" height="13">
                                       {{ date('d.m.y', strtotime($item->created_at)) }}
               
               
                                       <img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="14" height="14"> 
                                       {{ ($item->count) ? $item->count : '...' }} 
                                       {{ ($item->WorkType->type_desc == 'users') ? 'пользователей' : '' }}
                                       {{ ($item->WorkType->type_desc == 'groups') ? 'сообществ' : '' }}
                                       {{ ($item->WorkType->type_desc == 'socialnetworks') ? 'контактов' : '' }}
                                       {{ ($item->WorkType->type_desc == 'posts') ? 'постов' : '' }}
               
                      
                      
                                       </div>
                         </td>
                        <td>
                      
                        </td>
                        <td>
                        @if($item->WorkType->type_desc == 'users')
                            <a class="btn btn-warning btn-sm" href="{{route('cloud.edit', $item->track_id)}}">Изменить</a>
                            @endif
                        </td>
                        <td>
                            @if($item->WorkType->type_desc == 'users')
                            <a class="btn btn-success btn-sm" href="{{route('cloud.show', $item->track_id)}}">Перейти</a>
                            @endif
                        </td>
         
                        <td>
                            <form method="post" action="{{ route('cloud.destroy', $item['track_id'])}}">
                                @method('delete')
                                @csrf
                                <input type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')" value="Удалить">
                            </form>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>


<!--
        @forelse($data as $item)
        
    

        <div class="mywork"> 
            <div class="row">
            <div class="myworkheader">
                <span class="myworktitle">{{ $item->name }}</span>
            </div>

            <div class="col-md-7">
      
                <div class="myworkabout">
                    <div class="myworkname"><img src="https://zebra-target.ru/PNG/icon_cloud_alt.png" width="14" height="14"> {{ (isset($item->WorkType->type_name)) ? $item->WorkType->type_name : '' }}</div>
                    <div class="myworkname">
                    <div class="time">
                                   
                    <img src="https://zebra-target.ru/PNG/icon_calendar.png" width="13" height="13">
                        {{ date('d.m.y', $item->date) }}  


                        <img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="14" height="14"> 
                        {{ ($item->count) ? $item->count : '...' }} 
                        {{ ($item->WorkType->type_desc == 'users') ? 'пользователей' : '' }}
                        {{ ($item->WorkType->type_desc == 'groups') ? 'сообществ' : '' }}
                        {{ ($item->WorkType->type_desc == 'socialnetworks') ? 'контактов' : '' }}

       
       
                        </div>

                    </div>
            
                </div> 
            </div> 
            <div class="col-md-2 middle">
                @include('cloud._use')
            </div>
            <div class="col-md-3 middle">
                    @include('cloud._actions')
            </div>
            </div>
            @include('myworks._checkaccess')
        </div>
        @empty
            <h6>Здесь будут отображаться все облачные данные</h6>
        @endforelse
-->
    </div>
</div> 
 
@endsection