<?php
        include 'db.php';
        $pdo = new PDO($dsn, $db_user, $db_password, $options);


?>




Вам доступно для вывода:  
    <span class=new_people><?php echo  $_GET['summ']; ?> рублей</span>

<br><br>

<?php


        
// Если человек обновил платежные реквизиты
if ($_POST['money'] == 'on' )
{
    $value = array("sberbank" => $_POST['sberbank'], "yandex" => $_POST['yandex']);
    
    $sql = 'UPDATE partners SET sberbank=:sberbank,yandex=:yandex WHERE vk_id='.$_SESSION['user_id'];
    $statement = $pdo->prepare($sql);
    $statement->execute($value);
    
} elseif ($_POST['money'] == 'off' ) {
    
    // Записываем пользователя в БД
    $value = array("sberbank" => $_POST['sberbank'], "yandex" => $_POST['yandex'], "vk_id" => $_SESSION['user_id']);
    $sql = "INSERT INTO partners (vk_id, sberbank, yandex) VALUES (:vk_id, :sberbank, :yandex)";
    $statement = $pdo->prepare($sql);
    $statement->execute($value);
}


        // Запрашиваем информацию о пользователе
        $sql = 'SELECT * FROM partners WHERE vk_id='.$_SESSION['user_id'];
        $statement = $pdo->query($sql);
        $users = $statement->fetch(PDO::FETCH_ASSOC);

        if (!isset($users['sberbank']) and !isset($users['yandex'])) 
        {
            echo "У вас нет ни одного платежного реквизита!<br>Укажите их ниже: <br>";
            $money = "off";
        } else {
            
            $money = "on";
        }


?>



<br><br>
<div class='newenter_group'>
<form action="props_handler.php" method="post">
    <input type="hidden" name="withdraw" value="on">
    <input type="hidden" name="sberbank" value="<?php echo $users['sberbank'] ?>">
    <input type="hidden" name="yandex" value="<?php echo $users['yandex'] ?>">
    

   <?php if ($users['sberbank'] !== "0" or $users['yandex']!== "0") {
    echo "    <h6>Запросить выплату</h6>
    Сумма: 
    <input type=text size=6 name=reward value=".$_GET['summ'].">
    
    <p class=filtr>Выплатить на:</p>
<div class=filtr_param>
 <p><label><input type=radio name=card value=0 checked>  Сбербанк: ".$users['sberbank']."</label></p>
 <p><label><input type=radio name=card value=1> Яндекс: ".$users['yandex']."</label></p>
</div>
 <input class='btn btn-success' type=submit value='Запросить выплату' onclick=\"return confirm('Вы уверены?')\">
 ";
} else echo "<h5>Сперва добавьте реквизиты</h5>";
    ?>
    
    
    
</form>
</div>



<br><hr><br><h6>Ваши платежные реквизиты:</h6>
            <div class='newenter_group'>
            <form action='index.php?tab=props&summ=<?php echo $_GET['summ']; ?>' method='post'>
                <input type="hidden" name="money" value="<?php echo $money; ?>">
               Сбербанк:
                <input type='text' size='20' name='sberbank' placeholder=''  value='<?php echo $users['sberbank']; ?>' class='form-control' /> <br>
               Яндекс:
                <input type='text' size='20' name='yandex' placeholder='' value='<?php echo $users['yandex']; ?>' class='form-control' /> <br>
                <input type='submit' class='btn btn-info' value='Сохранить'>
            </form>
            </div>


