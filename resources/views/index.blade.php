<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
  
    <style>

body {
    background-color: #F3F4F6 !important;
}
.column {
    background-color: #FFFFFF;
}

.tab {
margin-top: 20px;
margin-bottom: 40px;
text-align: left;
padding: 40px;
background: #FDFDFD;
border-radius: 10px;
box-shadow:0 0px 10px rgba(117, 135, 156, 0.1); 
color: #2c343fde;
border-color: #222730;
}

.menu {
  margin-top: 40px;
  padding-top: 0px;
  margin-bottom: 50px;
}

.menu__title {
  font-size: 19px;
  color: #212529;
  padding-top: 11px;
  padding-bottom: 11px;
  padding-left: 5px;
 /* cursor: pointer; */
  margin-bottom: 0;
}
/*
.menu__title:hover {
  background-color: #ffffff;
  color: #000000;
  border-radius: 6px;
}*/

.menu__list {
  padding: 0;
  margin: 0;
  list-style-type: none;
}

.menu .nav-link {
  font-size: 11px;
  text-align: left;
  font-weight: bold;
  color: #454545;
  border: left;
  padding-top: 13px;
  padding-bottom: 13px;
  padding-left: 35px;
  margin-bottom: 5px;
}

.menu .nav-link:hover {
  background-color: #ffffff;
  color: #454545;

  box-shadow:0 0px 10px rgba(117, 135, 156, 0.1); 
  border-radius: 10px;
}

.visited {
  padding-left: 1px;
  color: #000000;
  border: left;

  box-shadow:0 0px 10px rgba(117, 135, 156, 0.1); 
  border-radius: 10px;
  background-color: #ffffff;
  padding: 5px;
  padding-top: 5px;
  padding-bottom: 5px;
}
.navbar {
    padding-left: 50px;
    padding-top: 20px;
    padding-bottom: 20px;
}
.navbar_left{
    text-align: left;
}

.navbar_right{
    text-align: right;
}
    </style>

</head>
  <body>
  <div class="container text-center">
  <div class="row">
  <nav class="navbar">
  <div class="navbar_left">
        <h3>ZEBRA</h3>
    </div>
    <div class="navbar_right">
        <a href="#" class="btn btn-primary btn-lg">Войти через ВК</a>
    </div>
  </nav>
    <div class="col-md-3">
    <nav class="menu">
        <ul class="nav flex-column">
        <li class="nav-item">
                <a href="#" class="nav-link">Поиск сообществ</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link visited">Недавно вступившие в группы</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Найти пользователей онлайн</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Сообщества</a>
            </li>
        </ul>
    </nav>
    </div>
    <div class="col-md-9 tab">

   <h3>Поиск и сбор целевой аудитории во ВКонтакте</h3><hr>
   Добро пожаловать на сервис!<br><br>

Зебра Таргет — инструмент для парсинга целевой аудитории из ВКонтакте. С помощью нашего сервиса можно искать целевую аудиторию для своего бизнеса, парсить пользователей, собирать базы для ретаргетинга и т.д.
<br><br>
Например, можно собирать сообщества, на которые подписана ваша целевая аудитория, собирать в них активность, отслеживать в них новых участников и так далее. </div>

  </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
  </body>
</html>