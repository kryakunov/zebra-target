
<br>
<div class="result_format">
<label class='label-checkbox'><input type="checkbox" class="checkbox" name="dogs" value="1" <?php if (isset($request['dogs'])) { echo 'checked'; } ?> > Исключить заблокированных (собачек)</label>
</div>

<label class='label'><b>Фильтр по полу:</b></label><br>
<div class="result_format">
<label class="cursor-pointer"><input class="radio" type="radio"  name="sex" value="0" checked> Любой пол</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="sex" value="2" <?php if (isset($request['sex']) and ($request['sex'] == "2") ) { echo 'checked'; } ?>> Только мужчины</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="sex" value="1" <?php if (isset($request['sex']) and ($request['sex'] == "1") ) { echo 'checked'; } ?>> Только женщины</label><br>
</div>


<label class='label'><b>Фильтр по семейному положению:</b></label><br>
  <div class="result_format">  <p>
     <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation1" value="1" <?php if (isset($request['relation1'])) { echo 'checked'; } ?> > Не женат, не замужем</label><Br>
     <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation2" value="2" <?php if (isset($request['relation2'])) { echo 'checked'; } ?>> Есть друг/есть подруга</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation3" value="3" <?php if (isset($request['relation3'])) { echo 'checked'; } ?>> Помолвлен/помолвлена</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation4" value="4" <?php if (isset($request['relation4'])) { echo 'checked'; } ?>> Женат/замужем</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation5" value="5" <?php if (isset($request['relation5'])) { echo 'checked'; } ?>> Всё сложно</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation6" value="6" <?php if (isset($request['relation6'])) { echo 'checked'; } ?>> В активном поиске</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation7" value="7" <?php if (isset($request['relation7'])) { echo 'checked'; } ?>> Влюблён/влюблена</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="relation8" value="8" <?php if (isset($request['relation8'])) { echo 'checked'; } ?>> В гражданском браке</label><Br>

</p>
</div>


<label class='label'><b>Статус дружбы с пользователем:</b></label><br>
  <div class="result_format">  <p>
     <label class='label-checkbox'><input type="checkbox" class="checkbox" name="friend_status0" value="0" <?php if (isset($request['friend_status0'])) { echo 'checked'; } ?> > Не является другом</label><Br>
     <label class='label-checkbox'><input type="checkbox" class="checkbox" name="friend_status1" value="1" <?php if (isset($request['friend_status1'])) { echo 'checked'; } ?>> Отправлена заявка/подписка пользователю</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="friend_status2" value="2" <?php if (isset($request['friend_status2'])) { echo 'checked'; } ?>> Имеется входящая заявка/подписка от пользователя</label><Br>
	 <label class='label-checkbox'><input type="checkbox" class="checkbox" name="friend_status3" value="3" <?php if (isset($request['friend_status3'])) { echo 'checked'; } ?>> Является другом</label><Br>

</p>
</div>


<label class='label'><b>Онлайн или оффлайн:</b></label><br>
<div class="result_format">
<label class="cursor-pointer"><input class="radio" type="radio"  name="online" value="0" checked>Всё равно</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="online" value="1" <?php if (isset($request['online']) and ($request['online']== "1") ) { echo 'checked'; } ?>>Онлайн</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="online" value="2" <?php if (isset($request['online']) and ($request['online'] == "2") ) { echo 'checked'; } ?>>Оффлайн</label><br>
</div> 

<label class='label'><b>Наличие аватарки:</b></label><br>
<div class="result_format">
<label class="cursor-pointer"><input class="radio" type="radio"  name="avatar" value="no" checked> Всё равно</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="avatar" value="1" <?php if (isset($request['avatar']) and ($request['avatar'] == "1") ) { echo ' checked'; } ?>> Обязательно должна быть</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="avatar" value="0" <?php if (isset($request['avatar']) and ($request['avatar'] == "0") ) { echo ' checked'; } ?>> Без аватары</label><br>
</div>

<label class='label'><b>Личные сообщения:</b></label><br>
<div class="result_format">
<label class="cursor-pointer"><input class="radio" type="radio"  name="ls" value="no" checked> Всё равно</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="ls" value="1" <?php  if (isset($request['ls']) and ($request['ls'] == "1") ) { echo 'checked'; } ?>> Открыты</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="ls" value="0" <?php if (isset($request['ls']) and ($request['ls'] == "0" )) { echo 'checked'; } ?>> Закрыты</label><br>
</div>



<label class='label'><b>Открытый или закрытый профиль:</b></label><br>
<div class="result_format">
<label class="cursor-pointer"><input class="radio" type="radio"  name="profile" value="0" checked> Всё равно</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="profile" value="1" <?php if (isset($request['profile']) and ($request['profile'] == "1") ) { echo 'checked'; } ?>> Открытый</label><br>
<label class="cursor-pointer"><input class="radio" type="radio"  name="profile" value="2" <?php if (isset($request['profile']) and ($request['profile'] == "2") ) { echo 'checked'; } ?>> Закрытый</label><br>
</div> 
<br>

<label class='label'><b>Ключевые слова в статусе:</b></label><br>
<small>(перечислите через запятую)</small>
<div class="form-group mb-3">       
    <textarea class="output-panel form-control"  name="keywords" rows="2" placeholder=""><?php if(isset($request['keywords'])) echo $request['keywords']; ?></textarea>
</div>

 	


<label class='label'><b>Фильтр по возрасту:</b></label><br>
<div class="result_format">
Возраст от: <select name="age_ot" class="textbox">
		<option value="0">Всё равно</option>
  <?php 
		  for ($i=1; $i <= 100; $i++) { 
		  
		  	if (isset($request['age_ot']) && $request['age_ot'] == $i) { echo "<option value=".$i." selected>".$i."</option>"; } 
		  	else echo "<option value=".$i.">".$i."</option>";
		  }
	?>
	</select> лет до: <select width="50" name="age_do" class="textbox">
		<option value="0">Всё равно</option>
  <?php 
		  for ($i=1; $i <= 100; $i++) { 

		  	if (isset($request['age_do']) && $request['age_do'] == $i) { echo "<option value=".$i." selected>".$i."</option>"; } 
		  	else echo "<option value=".$i.">".$i."</option>";
		  }
	?>
	</select> лет
 </div>
 <br>

    
 <label class='label'><b>Общие друзья:</b></label>
    <div class="result_format">
    	оставить людей, с которыми минимум <input type="text" size="6" name="n_common" class="textbox" placeholder=""  value="<?php if(isset($request['n_common'])) echo $request['n_common']; ?>"  /> общих друзей
	</div> 



	<br>
	<label class='label'><b>Подписчиков:</b></label><br>
     <div class="result_format">
    	от <input type="text" size="7" name="n_followers_ot" class="textbox" placeholder=""  aria-describedby="button-addon2" value={{isset($request['n_followers_ot']) ? $request['n_followers_ot'] : ''}}>
    	до <input type="text" size="7" name="n_followers_do" class="textbox" placeholder=""  aria-describedby="button-addon2"  value={{isset($request['n_followers_do']) ? $request['n_followers_do'] : ''}}> 
    	
    </div> 

	

<br>

	<label class='label'><b>Дата последнего захода</b></label>
    <div class="result_format">
    	Исключить тех, кто не заходил в ВК более чем
     <input type="text" size="3" name="n_day_online" class="textbox" placeholder=""  aria-describedby="button-addon2" value={{isset($request['n_day_online']) ? $request['n_day_online'] : ''}}>
 	дней 
 	</div>