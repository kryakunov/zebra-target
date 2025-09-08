
    <label class='label'>Минимум лайков:</label><br>
    <input type="text" size="4" name="likes_min"  class="textbox" value="{{isset($request['likes_min']) ? $request['likes_min'] : old('likes_min') }}" value=""/> 
<br><br>

<label class='label margin'>Искать посты начиная с даты:</label><br>

<input type="date" size="4" id="time_min"  class="textbox" name="time_min" value="{{isset($request['time_min']) ? $request['time_min'] : '' }}"> 
<br>


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

<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $day3.'\',\''.$time_max; ?>')">за три дня</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $week.'\',\''.$time_max; ?>')">за неделю</a>, 
<a style="cursor: pointer; color: #4185B8;" onclick="datachange('<?php echo $month.'\',\''.$time_max; ?>')">за месяц</a>, 
<a style="cursor: pointer; color: #AE0000;" onclick="datachange('')">сбросить</a><br>
<small><label class='label'>* Если не указано, то берутся посты за последний месяц</label></small>
<br><br>