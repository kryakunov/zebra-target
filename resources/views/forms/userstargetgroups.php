
<div class="row">
    <div class="col-md-5">
        <label class='label'><b>Ключевые слова в статусе:</b></label><br>
        <small>(перечислите через запятую)</small>
        <div class="form-group mb-3">       
            <textarea class="output-panel form-control"  name="keywords" rows="2" placeholder=""><?php if(isset($request['keywords'])) echo $request['keywords']; ?></textarea>
        </div>
    </div>
    <div class="col-md-4"><br><br>
        <label class='label'>Искать сообщества с ключевым словом среди первых
        <input type="text" size="4" name="count" class="textbox" value="50" aria-describedby="button-addon2" /> 
        сообществ</label>
    </div>

</div>

<label class='label'>Тип сообщества:</label>
        <div class="result_format">  
            <label class="cursor-pointer"><input checked class=radio name=type type=radio value=> Любой</label><br>		
            <label class="cursor-pointer"><input  class=radio  name=type type=radio value=group> Группа</label><br>
            <label class="cursor-pointer"><input  class=radio  name=type type=radio value=page> Страница</label><br>
        </div> 