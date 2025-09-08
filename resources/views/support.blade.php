@extends('layout')

@section('title') @parent Техническая поддержка @endsection

@section('content')
<div class="tab">
<div class="title">Техническая поддержка</div>
<div class="content">
Здесь вы можете задать свой вопрос или предложить что-то по сервису. <br>
Также вы можете задать свои вопросы или пообщаться с другими пользователями сайта в нашем <b><a href="https://t.me/zebratarget" target="_blank">телеграм-чате</a></b><br>
Или напишите мне лично в  <b><a href="https://t.me/kryakunov" target="_blank">телеграм</a></b><br><br>

@include('errors.exceptions')
@include('errors.session')

<form action="{{route('supportStore')}}" method="post" class="mb-4">
@csrf

<div class="form-group mb-3">    
    
    <label class='label'><b>Тема:</b></label><br>
    <input type="text" maxlength="42" size="52" name="topic" class="form-control  @error('topic') is-invalid @enderror" placeholder=""  value="{{ old('topic') }}" aria-describedby="button-addon2" />
    @error('topic') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <br>

    <label class='label'><b>Вопрос или предложение:</b></label><br>
    <textarea class="output-panel form-control @error('question') is-invalid @enderror"  name="question" rows="6" placeholder="">{{isset($request['question']) ? $request['question'] : null }}</textarea>
    @error('question') 
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Отправить">
</div>
</form> 
</div>

@if(count($data) > 0)
<div class="tab">
<div class="title">Мои вопросы: <b>{{count($data)}}</b></div>
    <div class="content">
        <table class='table'>
            <thead>
                <tr>
                    <th class='ShowGroupName'>Статус</th>
                    <th class='ShowGroupName'>Тема</th>
                    <th class='ShowGroupName'></th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr> 
                        <td width=20% class='{{$item['status'] == 1 ? 'payment' : 'ShowGroupName'}}'>{{$item['status'] == 0 ? 'На рассмотрении' : 'Закрыт'}}</td>
                        <td width=70% class='ShowGroupName'>{{$item['topic']}}</td>
                        <td><a href="{{route('supportShow', $item['id'])}}"><button class="btn btn-outline-secondary">Смотреть</button></a></td>
                      </tr>

                @endforeach
            </tbody>
        </table>
    </div>
</div> 
@endif








@endsection