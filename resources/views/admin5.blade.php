@extends('admin-layout')

@section('content')



@if(session('success'))
    <div class="alert alert-success" role="alert">
        {{session('success')}}
    </div> 
@endif

@if(session('danger'))
    <div class="alert alert-danger" role="alert">
        {{session('danger')}}
    </div> 
@endif



<div class="tab">
<div class="title">Последние обновления</div>
    <div class="content">

    
@if(isset($data))
    @foreach($data as $value)
        <label class="label"><b>{{ $value->title }}</b></label><br>
        <label class="label"><?php echo nl2br($value->text) ?></label><br>
        <label class="label">
            <img src="{{ $value->author->photo }}"  width="30" class="circle"> {{ $value->author->first_name }} {{ $value->author->last_name }}
        &nbsp;
            <img src="https://zebra-target.ru/PNG/icon_calendar.png" width="13" height="13"> {{ $value->created_at }}
        </label>

        @if($value->vk_id == session('id'))
        <a href="{{ route('deleteudpate', ['id' => $value->id]) }}" class="noborder right">Удалить</a>
        @endif
        <hr>
    @endforeach
@endif
<br><br>
<form action="{{route('updates')}}" method="post" class="mb-4">
    @csrf

    <input type="hidden" name="vk_id" value="{{ session('id') }}">
    <div class="form-group mb-3">    
        <label class='label'><b>Название:</b></label><br>
        <input type="text" maxlength="92" size="52" name="title" class="form-control  @error('topic') is-invalid @enderror" placeholder=""  value="{{ old('topic') }}" aria-describedby="button-addon2" />
        @error('topic') 
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <br>

        <label class='label'><b>Обновление</b></label><br>
        <textarea class="output-panel form-control @error('message') is-invalid @enderror"  name="message" rows="6" placeholder=""></textarea>
        @error('message') 
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Отправить">

</form> 


@endsection