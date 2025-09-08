
<label class='label'><b>Что собираем?</b></label>
<div class="result_format">
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="instagram" value="1" {{ isset($request['instagram']) ? 'checked' : '' }}></input> Instagram</label><Br>
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="facebook" value="1" {{ isset($request['facebook']) ? 'checked' : '' }}></input> Facebook</label><Br>
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="skype" value="1" {{ isset($request['skype']) ? 'checked' : '' }}></input> Skype</label><Br>
<label class='label-checkbox'><input type="checkbox" class='checkbox' name="twitter" value="1" {{ isset($request['twitter']) ? 'checked' : '' }} ></input> Twitter</label><Br>
</div>