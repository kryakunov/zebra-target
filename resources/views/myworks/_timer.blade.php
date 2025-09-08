

<div class="modal fade" id="timerModal{{ $item->id }}" tabindex="-1" aria-labelledby="timerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="timerModalLabel">Настройки запуска</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">

    <form action="{{ route('SaveWorkTimer', $item->id) }}" method="get" class="mb-4">
    @csrf

    <div class="form-check form-switch">
    <br><label class="form-check-label" for="flexSwitchCheckChecked{{$item->id}}">
        <input name="timer" class="form-check-input myradio" type="checkbox" id="flexSwitchCheckChecked{{$item->id}}" {{ ($item->timer == 1) ? 'checked' : '' }}>
        Запускать по таймеру </label><br><br>


        Запускать с интервалом раз в
        <select name="timer_count" class="textbox">
            <?php 
            for($i = 1; $i < 8; $i++) {
                echo "<option value='".$i."' ";
                if ($i == $item->timer_count) echo "selected='selected'";
                echo " >$i</option>";
            }
            ?>
        </select> дней
<br><br>      <label class="label">
        * Интервал отсчитывается c момента последнего запуска
        </label>
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