<div class="modal fade" id="sampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Сохранить шаблон</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">

        <form action="{{route('savesample', ['id' => session('chainId')])}}" method="get" class="mb-4">
        @csrf
        <br>
        Название шаблона: <input type="text" name="name" class="form-control">
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <input type="submit" class="btn btn-primary" value="Сохранить">
        </form>
        </div>
      </div>

  </div>
</div>