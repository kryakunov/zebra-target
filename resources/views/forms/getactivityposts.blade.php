

<label class='label'><b>Какие активности собираем?</b></label> 
<div class="result_format"> 
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="likes" value="1"  {{isset($request['likes']) ? 'checked' : null }}></input> Лайки</label><br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="comments" value="1"  {{isset($request['comments']) ? 'checked' : null }}></input> Комментарии</label><br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="thread_comments" value="1" {{isset($request['thread_comments']) ? 'checked' : null }}></input> Комментарии > комментарии</label><Br>
</div>