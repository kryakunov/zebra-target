
<label class='label'><b>Кого собираем?</b></label>
<div class="result_format">
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="couples" value="1" {{ isset($request['couples']) ? 'checked' : '' }}></input> Вторых половинок (муж/жена, парень/девушка)</label><Br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="child" value="1" {{ isset($request['child']) ? 'checked' : '' }}></input> Детей</label><Br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="sibling" value="1" {{ isset($request['sibling']) ? 'checked' : '' }}></input> Братьев/Сестер</label><Br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="parent" value="1" {{ isset($request['parent']) ? 'checked' : '' }}></input> Родителей</label><Br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="grandparent" value="1" {{ isset($request['grandparent']) ? 'checked' : '' }} ></input> Дедушек/Бабушек</label><Br>
    <label class='label-checkbox'><input type="checkbox" class='checkbox' name="grandchild" value="1" {{ isset($request['grandchild']) ? 'checked' : '' }} ></input> Внуков</label><Br>
</div>