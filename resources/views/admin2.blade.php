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



@if(count($questions) > 0)
<div class="tab">
<div class="title">Вопросов: <b>{{count($questions)}}</b></div>
    <div class="content">

        @foreach($questions as $item)
        <img src="{{ $users[$item['vk_id']]['photo'] }}" class="circle">
            {{ $users[$item['vk_id']]['first_name'] }}
            {{ $users[$item['vk_id']]['last_name'] }}
            
                <label class='label'><b>Тема: {{ $item->topic }}</b></label><br><br>

                @foreach($item->questions as $question)
                    <?php if($question->role == 0) $role = 'warning'; else $role = 'info'; ?>
                    <div class="alert alert-{{$role}}" role="alert">
                        {{ $question->text }}
                    </div>
                @endforeach

            
            <form action="{{route('supportReply', $item['id'])}}" method="post">
            @csrf

			<label class='label-checkbox'><input type="checkbox" class='checkbox' name="closed" value="1" ></input> Закрыть вопрос?</label><Br>

            <div class="form-group mb-3">       
                <textarea class="output-panel form-control @error('reply') is-invalid @enderror"  name="reply" rows="2" placeholder="">{{isset($request['reply']) ? $request['reply'] : null }}</textarea>
                @error('question') 
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <input class="btn btn-success btn_size" type="submit" id="btnMenu" value="Ответить">
            </form><br><br>
        @endforeach

    </div>
</div> 
@endif


@endsection