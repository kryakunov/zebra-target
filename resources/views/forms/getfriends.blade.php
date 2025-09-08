
<label class='label'><b>Кого собираем?</b></label> 
<div class="result_format"> 
    <div class="filtr_param">
        <label class="label-checkbox"><input type="checkbox" class="checkbox" name="friends" value="checked"   {{isset($request['friends']) ? 'checked' : null }}>Друзей</label><br>
        <label class="label-checkbox"><input type="checkbox" class="checkbox" name="followers" value="checked"   {{isset($request['followers']) ? 'checked' : null }}>Подписчиков</label>
    </div>
</div>