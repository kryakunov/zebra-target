
    <div class="dropdown">
        <button class="submitbutton right dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Действия
        </button>
            
    <ul class="dropdown-menu " aria-labelledby="dropdownMenuButton1">

    <li class="drop-menu item-drop-menu"><a href="{{route('download', ['id' => $item->id])}}" class="dropdown-item">Сохранить в файл</a></li>
        <li class="drop-menu item-drop-menu"><a href="" data-bs-toggle="modal" data-bs-target="#timerModal{{ $item->id }}" class="dropdown-item">Настройки автозапуска</a></li>
      @if($item->vk_id == session('id'))
        <li class="drop-menu item-drop-menu"><a href="" data-bs-toggle="modal" data-bs-target="#exampleModal{{ $item->id }}" class="dropdown-item">Поделиться задачей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('workstore', $item['id'])}}" class="dropdown-item">Сохранить в мои базы</a></li>
        <li><hr class="dropdown-divider"></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('workdelete', ['id' => $item['id']])}}"  class="dropdown-item">Удалить задачу</a></li>
      @endif

</ul>
</div>

@include('myworks._timer')

<div class="modal fade" id="exampleModal{{ $item->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Поделиться задачей</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">

    <form action="{{route('sharework', ['id' => $item->id] )}}" method="get" class="mb-4">
    @csrf

    <div class="form-check form-switch">
    <br><label class="form-check-label" for="flexSwitchCheckChecked{{$item->id}}">
        <input name="share" class="form-check-input myradio" type="checkbox" id="flexSwitchCheckChecked{{$item->id}}" {{ ($item->share == 1) ? 'checked' : '' }}>
        Открыть задачу для просмотра другим пользователям </label><br><br>

      </div>

           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <input type="submit" class="btn btn-primary" value="Сохранить">
    </form>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="exampleModal2{{ $item->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Поделиться цепочкой</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">

    <form action="{{route('sharechain', ['id' => $item->id] )}}" method="get" class="mb-4">
    @csrf

    <div class="form-check form-switch">
    <br>
        
        @if($item->parent_id) 
        <label class="form-check-label" for="flexSwitchCheckChecked{{$item->id}}">
        <input name="share_parent" class="form-check-input myradio" type="checkbox" id="flexSwitchCheckChecked{{$item->id}}" {{ ($item->share_parent == 1) ? 'checked' : '' }}>
        Окрыть всю цепочку задач для просмотра </label><br>
        @endif
      </div>

           
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <input type="submit" class="btn btn-primary" value="Сохранить">
    </form>
      </div>
    </div>
  </div>
</div>