
<?php

$textarea = htmlspecialchars($_POST['groupspeople']);
$error = false;

if (!$_SESSION['token'])
	echo "<div class='error'>Для начала работы необходимо войти через ВК. </div>";


echo "<div class='desc_script'><p class='how_script'>Как работает этот скрипт?</p>
	Этот скрипт находит все сообщества и паблики на которые подписаны люди.</div>";


?>
<div class="form-group">
<form class="parser" action="index.php?tab=groupspeople" method="post">

    <div class="form-group">
        <textarea class="output-panel form-control" id="exampleFormControlTextarea1"  name="textarea" rows="5" placeholder="Вставьте сюда ID пользователей"><?php echo $_POST['textarea']; ?></textarea>
    </div>

<p class="filtr">Искать только:</p>
<div class="filtr_param">

     <label><input type="checkbox" name="sp1" value="a1" <?php if (isset($_POST['sp1'])) echo 'checked'; ?> >Паблики</label><Br>
     <label><input type="checkbox" name="sp2" value="a2" <?php if (isset($_POST['sp2'])) echo 'checked'; ?> >Группы</label><Br>

</div>



    <p class="filtr">Результаты в формате:</p>
		<div class="filtr_param">

                <p><label><input name="result_format" type="radio" value="id"
                 <?php if (($_POST['result_format'] == 'id') or (!isset($_POST['result_format']))) echo 'checked'; ?> >
            ID сообществ </label></p>

                <p><label><input name="result_format" type="radio" value="vk_com_id"
                 <?php if ($_POST['result_format'] == 'vk_com_id') echo 'checked'; ?> >
			Ссылки вида vk.com/club12345...</label></p>

                <p><label><input name="result_format" type="radio" value="names"
                 <?php if ($_POST['result_format'] == 'names') echo 'checked'; ?> >
			Только названия сообществ</label></p>

                <p><label><input name="result_format"  type="radio" value="name_id"
                 <?php  if ($_POST['result_format'] == 'name_id') echo 'checked'; ?> >
			 Названия и ссылки</label></p>

                <p><label><input name="result_format"  type="radio" value="photo"
                 <?php if ($_POST['result_format'] == 'photo') echo 'checked'; ?> >
			Кликабельные аватарки сообществ</label></p>
		</div>
<br>


   <input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Начать поиск сообществ"  onclick="change()">
    <br>
</form>
<br>

 <label htmlFor="exampleFormControlTextarea1">Результат:</label><a name="bottom"></a>
<?php if ($_POST['result_format'] !== 'photo') echo "<textarea class='output-panel form-control' id='exampleFormControlTextarea1' rows='10' placeholder=''>";



// Обращаемся к АПИ ВК и парсим

if ( isset($_POST['textarea'])  )
{

if (!isset($_SESSION['token'])) { exit('Сперва авторизуйтесь'); }

    if (isset($_POST['sp1'])) $type = 'publics';
    if (isset($_POST['sp2'])) $type = $type . ',groups';

    if ($type[0] == ',')
        {

            mb_internal_encoding("UTF-8");
            $type = mb_substr( $type, 1);
        }

echo $itog_type;
$textarea = explode("\n", $_POST['textarea']);
$count = count($textarea);
$c = 0;
$itog = array();
$slp = 0;

do {


                        // Если это третья итерация, делаем паузу в 1 секунду перед следующим запросом к ВК АПИ
                    /*if ( $slp > 9)
                        {
                        sleep(1);
                        $slp = 0;
                        }   */

    $textarea[$c] = str_replace(" ", "", $textarea[$c]);
    $textarea[$c] = preg_replace('/\r \n|\r|\n/u', '', $textarea[$c]);
if (!is_numeric($textarea[$c])) continue;
        $url = "https://api.vk.com/method/groups.get?user_id=".$textarea[$c]."&v=5.52&count=1000&filter=".$type."&extended=1&access_token=" . $_SESSION['token'];
        $result = json_decode(file_get_contents($url),true);
        $people = $result['response']['count']; // В переменную people записываем сколько всего  человек состоит в группе
        $result = $result['response']['items']; // В переменную result получаем весь массив данных о пользователях


        $c++;
        if (!$result) continue;









        switch ($_POST['result_format']) {

        case 'id': foreach ($result as $value)  {   $itog[] =  $value['id'];  } break;
        case 'vk_com_id': foreach ($result as $value)  {  $itog[] =  'vk.com/club'.$value['id'];     } break;
        case 'names': foreach ($result as $value)  {  $itog[] =  $value['name'];     } break;
        case 'name_id': foreach ($result as $value)  {  $itog[] =  $value['name']." | vk.com/club".$value['id'];     } break;

        case 'photo':  foreach ($result as $value)  {
           $itog[] = '<a href=https://vk.com/club'.$value['id'].' target=blank><img src='.$value['photo_50'].' class=vk-user-photo-left width=50 height=50></a> ';     }  break;
        }




} while ($c < $count);




$itog = array_unique($itog);

if ($_POST['result_format'] == 'photo') echo "</textarea> Найдено групп: ".count($itog)."  <div class='newenter_group'>";

if ($_SESSION['access'] == '0')
    {
    $itog = array_slice($itog, 0, 50);
    echo "У вас бесплатный доступ. Вам доступны только первые 50 значений. \n---------------\n";
    foreach ($itog as $value) {
        echo $value . "\n";
        }
    } else {

        foreach ($itog as $value) {
        echo $value . "\n";
        }
    }






}

if ($_POST['result_format'] == 'photo') echo '</div>'; else echo "</textarea> <br><br>Просмотрено пользователей: ".count($textarea)."<br>Найднено групп: ". count($itog);


/*
$market = isset($_POST['market']) ? '1' : '0';







		// Если запрос неудачный, прерываем итерацию
		if (!$result)
			{
			$i++;
			continue;
			}



if ($_SESSION['access'] == '0')
{
    $i = 1;
    if ($_POST['result_format'] !== 'photo') echo "У вас бесплатный доступ. Вам доступны только первые 15 значений. \n---------------\n";

    switch ($_POST['result_format']) {
    case 'id': foreach ($result as $value)  {   echo $value['id'] . "\n"; $i++; if ($i > 15) break; } break;
    case 'vk_com_id': foreach ($result as $value)  {  echo 'vk.com/club'.$value['id'] . "\n";  $i++; if ($i > 15) break;   } break;
    case 'names': foreach ($result as $value)  {  echo $value['name'] . "\n";   $i++; if ($i > 15) break;  } break;
    case 'name_id': foreach ($result as $value)  {  echo $value['name']." | vk.com/club".$value['id'] . "\n";  $i++; if ($i > 15) break;   } break;
    case 'photo': echo "</textarea>".count($result)." групп <div class='newenter_group'>"; foreach ($result as $value)  {
        echo '<a href=https://vk.com/club'.$value['id'].' target=blank><img src='.$value['photo_50'].' class=vk-user-photo-left width=50 height=50></a> ' . "\n";   $i++; if ($i > 15) break;  }  echo '</div>'; break;
    }

} else {

        switch ($_POST['result_format']) {
    case 'id': foreach ($result as $value)  {   echo $value['id'] . "\n";  } break;
    case 'vk_com_id': foreach ($result as $value)  {  echo 'vk.com/club'.$value['id'] . "\n";     } break;
    case 'names': foreach ($result as $value)  {  echo $value['name'] . "\n";     } break;
    case 'name_id': foreach ($result as $value)  {  echo $value['name']." | vk.com/club".$value['id'] . "\n";     } break;
    case 'photo': echo "</textarea>".count($result)." групп <div class='newenter_group'>"; foreach ($result as $value)  {
        echo '<a href=https://vk.com/club'.$value['id'].' target=blank><img src='.$value['photo_50'].' class=vk-user-photo-left width=50 height=50></a> ' . "\n";     }  echo '</div>'; break;
    }

}

	// Записываем в файл $group_id
    $str =date("d.m.y") . ' > ' . $_SESSION['user_id'] . ' > Search Q ' .   PHP_EOL;
    $fd = fopen("log/all.txt", 'a+');
    fwrite($fd, $str);
    fclose($fd);

}

if ($_POST['result_format'] !== 'photo')
        echo '</textarea><br>Всего найдено сообществ: '.count($result);
if ($_SESSION['access'] == '0') echo "<br>Вам доступны только первые 15 значений <a href=/index.php?tab=price target=blank> Приобрести полный доступ</a>";

*/

?>


    <br><br>



