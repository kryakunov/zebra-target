@extends('layout')

@section('title') @parent Сбор друзей и подписчиков @endsection

@section('content')
<div class="tab">
<div class="title">Сбор друзей и подписчиков</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.session')


<form action="{{route('testpagepost')}}" method="post" class="mb-4">

<label class='label'>Откуда подгружаем данные?</label> 
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Из формы</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Из облака</button>
  </li>

</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">


            @csrf

            @if(isset($work))
                @include('_work')
            @else

<br>

                <textarea  class="output-panel form-control @error('users') is-invalid @enderror"  name="users" rows="8" placeholder="По одному ID в строке">{{isset($request['users']) ? $request['users'] : ''}}{{ old('users') }}</textarea>
                @error('users') 
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror


                <br>

                
  </div>
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

  @include('_chooseUpload')   


  </div>



<label class='label'><b>Кого собираем?</b></label> 
<div class="result_format"> 
    <div class="filtr_param">
        <label class="label-checkbox"><input type="checkbox" class="checkbox" name="friends" value="checked"   {{isset($request['friends']) ? 'checked' : null }}>Друзей</label><br>
        <label class="label-checkbox"><input type="checkbox" class="checkbox" name="followers" value="checked"   {{isset($request['followers']) ? 'checked' : null }}>Подписчиков</label>
    </div>
</div>



<label class='label'><b>Название задачи:</b><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder="Без названия"  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br>

	<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск "  onclick="change()">


  
 
        @endif
</form>


<script type="text/javascript">
    function ons(){
        var form = document.getElementById('formm');
        form.innerHTML = '<input type="text" class="form-control" name="city" id="city" list="cities">';
    }
  

</script>
@if(isset($data))
    <div class="alert alert-success">
        Найдено: <b>{{count($data)}}</b>
    </div>
        <textarea class="output-panel form-control mb-2"  id="textarea" rows="12">@foreach($data as $id)
{{$id}}
@endforeach</textarea>
    <button type="button" class="btn btn-outline-dark margin"  onclick="copy()">Скопировать</button> <br><br>
@endif


@endsection