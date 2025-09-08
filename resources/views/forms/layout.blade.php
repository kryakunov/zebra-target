@extends('layout')

@section('title') @parent @endsection

@section('content')
<div class="tab">
<div class="title"> {{ $page->type_name }} </div>
<div class="content">

@include('_ScriptDesk')
@include('errors.session')
@include('errors.exceptions')

<form action="{{ route(mb_strtolower($page->type)) }}" method="post" class="mb-4">
@csrf

@if(isset($work))
	@include('_work')
@else
    @include('forms._users')
@endif


@include('forms.'.mb_strtolower($page->type)) 

<br><br>
<label class='label'>
    <b>Придумайте название задачи:</b>
</label><br>
 <input type="text" maxlength="42" size="52" name="name" class="textbox margin" placeholder=""  value="{{ old('name') }}" aria-describedby="button-addon2" />
<br><br>

<button class="btn btn-success btn_size" type="submit"  id="btnMenu" value="Создать задачу"  onclick="change()">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
  <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
</svg>
&nbsp; 
Создать задачу</button>
</form> 


</div></div>
@endsection