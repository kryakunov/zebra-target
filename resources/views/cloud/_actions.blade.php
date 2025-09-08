
    <div class="dropdown">
        <button class="submitbutton right dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Действия
        </button>
            
    <ul class="dropdown-menu " aria-labelledby="dropdownMenuButton1">
        @if($item->WorkType->type_desc == 'users')
        <li class="drop-menu item-drop-menu"><a href="{{route('cloud.show', $item->track_id)}}" class="dropdown-item">Открыть базу</a></li>
        @endif
        <li class="drop-menu item-drop-menu"><a href="{{route('download', ['id' => $item->id])}}" class="dropdown-item">Сохранить в файл</a></li>
        @if($item->vk_id == session('id'))
        <li class="drop-menu item-drop-menu"><a href="" data-bs-toggle="modal" data-bs-target="#exampleModal{{ $item->id }}" class="dropdown-item">Поделиться задачей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('workdelete', ['id' => $item['id']])}}" onclick="return confirm('Вы уверены?')" class="dropdown-item">Удалить задачу</a></li>
        @endif
</ul>
</div>
