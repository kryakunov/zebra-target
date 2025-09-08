
<?php

if ($work->type == 'usersfilter')
{

if (isset($request['sex'])) {
    echo 'Фильтр по полу: ';
    echo ($request['sex'] == 0) ? 'любой' : '';
    echo ($request['sex'] == 1) ? 'женский' : '';
    echo ($request['sex'] == 2) ? 'мужской' : '';
}

if (isset($request['relation0'])) { $sp[] = ' Не женат, не замужем'; }
if (isset($request['relation1'])) { $sp[] =  'Есть друг/есть подруга'; }
if (isset($request['relation2'])) { $sp[] =  'Помолвлен/помолвлена'; }
if (isset($request['relation3'])) { $sp[] =  'Женат/замужем'; }
if (isset($request['relation4'])) { $sp[] =  'Всё сложно'; }
if (isset($request['relation5'])) { $sp[] =  'В активном поиске'; }
if (isset($request['relation6'])) { $sp[] =  'Влюблён/влюблена'; }
if (isset($request['relation7'])) { $sp[] =  'В гражданском браке'; }

if (isset($sp)) {
    echo '<br><br>Семейное положение: <br>';
    foreach($sp as $key => $val) {
        echo $val . '<br>';
    }
}

if (isset($request['friend_status0'])) { $fstatus[] = ' Не является другом'; }
if (isset($request['friend_status1'])) { $fstatus[] =  'Отправлена заявка/подписка пользователю'; }
if (isset($request['friend_status2'])) { $fstatus[] =  'Имеется входящая заявка/подписка от пользователя'; }
if (isset($request['friend_status3'])) { $fstatus[] =  'Является другом'; }

if (isset($fstatus)) {
    echo '<br><br>Статус дружбы с пользователем: <br>';
    foreach($fstatus as $key => $val) {
        echo $val . '<br>';
    }
}

if (isset($request['online']) and $request['online'] != 0) {
    echo '<br><br>Онлайн или оффлайн: ';
    echo ($request['online'] == 1) ? 'Онлайн' : '';
    echo ($request['online'] == 2) ? 'Оффлайн' : '';
}

if ($request['avatar'] !== 'no') {
    echo '<br><br>Наличие аватарки: ';
    echo ($request['avatar'] == 1) ? 'Обязательно должна быть' : '';
    echo ($request['avatar'] == 0) ? 'Без аватары' : '';
}


if ($request['ls'] !== 'no') {
    echo '<br><br>Личные сообщения: ';
    echo ($request['ls'] == 1) ? 'Открыты' : '';
    echo ($request['ls'] == 2) ? 'Закрыты' : '';
}


if ($request['profile'] !== '0') {
    echo '<br><br>Открытый или закрытый профиль: ';
    echo ($request['profile'] == 1) ? 'Открытый' : '';
    echo ($request['profile'] == 2) ? 'Закрытый' : '';
}

if (isset($request['keywords'])) {
    $keywords = explode("\r\n", $request['keywords']);
    $keywords = implode(", ", $keywords);
    echo '<br><br>Ключевые слова в статусе: ' . $keywords;
}

if ($request['age_ot'] !== '0') {
    echo '<br><br>Возраст от: ' . $request['age_ot'];
}

if ($request['age_do'] !== '0') {
    echo '<br><br>Возраст до: ' . $request['age_do'];
}

if (isset($request['n_common'])) {
    echo '<br><br>Общих друзей от: ' . $request['n_common'];
}


if (isset($request['n_followers_ot'])) {
    echo '<br><br>Подписчиков от: ' . $request['n_followers_ot'];
}

if (isset($request['n_followers_do'])) {
    echo '<br><br>Подписчиков до: ' . $request['n_followers_do'];
}

if (isset($request['n_common'])) {
    echo '<br><br>Исключить тех, кто не заходил в ВК более чем: ' . $request['n_day_online'] . ' дней';
}
}
?>
