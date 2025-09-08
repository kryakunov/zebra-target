
<label class='label'><b>Какие посты собирать?</b></label><br>
  <div class="result_format">  <p>
    <label class="cursor-pointer"><input class="radio" type="radio"  name="when_posts" value="0" checked>Всё равно</label><br>
    <label class="cursor-pointer"><input class="radio" type="radio"  name="when_posts" value="1" <?php if (isset($_POST['when_posts']) and ($_POST['when_posts']== "1") ) { echo 'checked'; } ?>>Только посты пользователей</label><br>
    <label class="cursor-pointer"><input class="radio" type="radio"  name="when_posts" value="2" <?php if (isset($_POST['when_posts']) and ($_POST['when_posts'] == "2") ) { echo 'checked'; } ?>>Только посты сообществ</label><br>
    <label class="cursor-pointer"><input class="radio" type="radio"  name="when_posts" value="3" <?php if (isset($_POST['when_posts']) and ($_POST['when_posts'] == "3") ) { echo 'checked'; } ?>>Собрать только ID авторов постов</label><br>
    </div> 


<label class='label'>Количество <b>лайков</b> у поста:</label><br>
<div class="result_format">
<div class="row">
    <div class="col-md-2">
        <input type="text" size="4" name="likes_min"  class="form-control"  placeholder="от" value="{{isset($request['likes_min']) ? $request['likes_min'] : old('likes_min') }}" aria-describedby="button-addon2" /> 
    </div>
    <div class="col-md-2">
        <input type="text" size="4" name="likes_max"  class="form-control"  placeholder="до" value="{{isset($request['likes_max']) ? $request['likes_max'] : old('likes_max') }}" value="" aria-describedby="button-addon2" /> 
    </div>
</div>
</div>
<br>

<label class='label'>Количество <b>репостов</b> у поста:</label><br>
<div class="result_format">
<div class="row">
    <div class="col-md-2">
        <input type="text" size="4" name="reposts_min"  class="form-control"  placeholder="от" value="{{isset($request['reposts_min']) ? $request['reposts_min'] : old('reposts_min') }}" aria-describedby="button-addon2" /> 
    </div>
    <div class="col-md-2">
        <input type="text" size="4" name="reposts_max"  class="form-control"  placeholder="до" value="{{isset($request['reposts_max']) ? $request['reposts_max'] : old('reposts_max') }}" value="" aria-describedby="button-addon2" /> 
    </div>
</div>
</div>
<br>

<label class='label'>Количество <b>просмотров</b> у поста:</label><br>
<div class="result_format">
<div class="row">
    <div class="col-md-2">
        <input type="text" size="4" name="views_min"  class="form-control"  placeholder="от" value="{{isset($request['views_min']) ? $request['views_min'] : old('views_min') }}" aria-describedby="button-addon2" /> 
    </div>
    <div class="col-md-2">
        <input type="text" size="4" name="views_max"  class="form-control"  placeholder="до" value="{{isset($request['views_max']) ? $request['views_max'] : old('views_max') }}" value="" aria-describedby="button-addon2" /> 
    </div>
</div>
</div>
<br>

<label class='label'>Количество <b>комментариев</b> у поста:</label><br>
<div class="result_format">
<div class="row">
    <div class="col-md-2">
        <input type="text" size="4" name="comments_min"  class="form-control"  placeholder="от" value="{{isset($request['comments_min']) ? $request['comments_min'] : old('comments_min') }}" aria-describedby="button-addon2" /> 
    </div>
    <div class="col-md-2">
        <input type="text" size="4" name="comments_max"  class="form-control"  placeholder="до" value="{{isset($request['comments_max']) ? $request['comments_max'] : old('comments_max') }}" value="" aria-describedby="button-addon2" /> 
    </div>
</div>
</div>
<br>


<label class='label margin'><b>Учитывать только те посты, которые были опубликованы в период:</b></label>
<div class='row'>
	<div class='col-md-3'>
<input type="date" size="3" id="time_min"  class="form-control" name="time_min" value="{{isset($request['time_min']) ? $request['time_min'] : null }}">
	</div>
	<div class='col-md-3'>
<input type="date" size="3"  id="time_max"  class="form-control" name="time_max" value="{{isset($request['time_max']) ? $request['time_max'] : null }}">
	</div>
</div> 

<?php
	$time_max = date('Y-m-d');
	$date = new DateTime('-1 days');
	$day = $date->format('Y-m-d');

	$date = new DateTime('-3 days');
	$day3 = $date->format('Y-m-d');

	$date = new DateTime('-7 days');
	$week = $date->format('Y-m-d');

	$date = new DateTime('-1 month');
	$month = $date->format('Y-m-d');
?>

<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day.'\',\''.$time_max; ?>')">день</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day3.'\',\''.$time_max; ?>')">три дня</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $week.'\',\''.$time_max; ?>')">неделя</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $month.'\',\''.$time_max; ?>')">месяц</a>, 
<a style="cursor: pointer; color: #AE0000;" onclick="datachange('')">сбросить</a>
<br>