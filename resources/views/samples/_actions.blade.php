<div class="dropdown">
    <button class="submitbuttontwo dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
        Действия
    </button>
            
    <ul class="dropdown-menu " aria-labelledby="dropdownMenuButton1">
        <li class="drop-menu item-drop-menu"><a href="{{route('SampleDownload', ['id' => $data->id])}}" class="dropdown-item">Сохранить в файл</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('SampleStoreInCloud', $data['id'])}}" class="dropdown-item">Сохранить в мои базы</a></li>
        </ul>
</div>