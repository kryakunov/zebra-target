<div class="work-blade">
    <p class="work-blade-title">Исходные данные: задача «{{ $work->name }}»</p>
    @if(session('access') < time()) 
    <div class='MyWorkFreeAccess'>
        <img src='https://zebra-target.ru/PNG/icon_lock_alt.png' width='16' height='16'> 
        У вас бесплатный доступ. В задачу уйдут только первые 
        {{ ($work->WorkType->type_desc == 'users') ? '50 пользователей ' : '' }}
        {{ ($work->WorkType->type_desc == 'groups') ? '10 сообществ ' : '' }}
        {{ ($work->WorkType->type_desc == 'posts') ? '15 постов ' : '' }}
         из {{ $work->count }} найденных  <a href=price target=_blank class=nodecoration> Приобрести полный доступ</a>
    </div>
    @else
    <img src="https://zebra-target.ru/PNG/icon_documents_alt.png" width="14" height="14"> 
        {{ $work->count }}
        {{ ($work->WorkType->type_desc == 'users') ? ' пользователя ' : '' }}
        {{ ($work->WorkType->type_desc == 'groups') ? ' сообществ ' : '' }}
        {{ ($work->WorkType->type_desc == 'posts') ? ' постов ' : '' }}
    @endif

        <input type="hidden" name="parentId" value="{{ $work->id }}">
    @if(isset($type))
        <input type="hidden" name="workType" value="{{ $type }}">
    @endif

</div>