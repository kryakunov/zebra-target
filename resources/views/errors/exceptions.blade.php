

@if (!empty($exceptions))
    @foreach ($exceptions as $exception)
    <div class="alert alert-danger">
        {{ $exception }}
    </div>
    @endforeach
@endif

@if (!empty(session()->get('exceptions')))
    
    @foreach(session()->get('exceptions') as $exception)
    <div class="alert alert-danger">
        {{ $exception }}<br>
    </div>
    @endforeach

@endif


@if (!empty(session()->get('addedGroups')))
    @foreach (session()->get('addedGroups') as $group)
    <div class="alert alert-success">
        Сообщество <img src="{{$group['photo']}}" width="30" class="circle"> <b>{{$group['name']}}</b> добавлено в остлеживание
    </div>
    @endforeach
@endif


@if (!empty(session()->get('alreadyTracked')))
    @foreach (session()->get('alreadyTracked') as $group)
    <div class="alert alert-danger">
        Сообщество <img src="{{$group['photo']}}" width="30" class="circle"> <b>{{$group['name']}}</b> уже отслеживается
    </div>
    @endforeach
@endif