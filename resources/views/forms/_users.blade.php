
<div class="row">

    <div class="col-md-8">
        <div class="form-group no-margin" style="width: 100%;">
            <div id="works" style="display:none;">
                <select class="form-control"  name="works">
                    <option value="0">- Выберите задачу -</option>
                    @foreach($works as $work)
                        <option value="{{ $work->id }}">{{ $work->name }} ({{ $work->count }} ID)</option>
                    @endforeach

                </select>
            </div> 
            <div id="lists" style="display:none;">
                <select class="form-control" name="lists">
                    <option value="0">- Выберите список -</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->count }} ID)</option>
                    @endforeach
                </select>
            </div>
            <div id="form">
                <div class="form-group mb-3">
                    <textarea class="output-panel form-control @error('form') is-invalid @enderror"  name="form" rows="5" placeholder="Вставьте данные по одному значению на строку"></textarea>
                    @error('form')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 left">
        <label class='label'><b>Откуда берем данные?</b></label>
        <div class="result_format">
            <div>
                <label>
                    <input class="choose" data-val="true"  id="ProjectBind_ProjectBindType" name="data_from" type="radio" value="form" checked/> Из формы&emsp;
                </label>
            </div>
            <div>
                <label>
                    <input  class="choose" data-val="true"  id="ProjectBind_ProjectBindType" name="data_from" type="radio" value="works" @if(count($works) < 1) disabled @endif/> Из моих задач&emsp;
                </label>
            </div>
            <div>
                <label>
                    <input  class="choose" data-val="true"  id="ProjectBind_ProjectBindType" name="data_from" type="radio" value="lists" @if(count($lists) < 1) disabled @endif/> Из моих списков&emsp;
                </label>
            </div>
        </div>
    </div>
    
</div><br>

<script type="text/javascript">

    $('input.choose').change(function () {
        if ($(this).val() == "form") {
            $('#works').hide();
            $('#lists').hide();
            $('#form').show();
        } if ($(this).val() == "works") {
            $('#works').show();
            $('#lists').hide();
            $('#form').hide();
        } if ($(this).val() == "lists") {
            $('#works').hide();
            $('#form').hide();
            $('#lists').show();
        }
    });
</script>