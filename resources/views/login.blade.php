<h1>Hey! <a href="{{$browser_url}}">go</a></h1><hr>

@if(session('success'))
    {{session('success')}}
@endif
<hr>
@if(session('token'))
    hello, {{session('last_name')}}
@endif