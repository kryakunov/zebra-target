@extends('layout')

@section('title') @parent Анализ аудитории сообщества @endsection

@section('content')
<div class="tab">
<div class="title">Анализ аудитории сообщества</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.validate')
@include('errors.session')

<form action="{{route('analizStore')}}" method="post" class="mb-4">
@csrf

<div class="input-group">
      <input type="text" name="group" class="form-control" placeholder="Введите URL или ID сообщества" value="{{isset($request['group']) ? $request['group'] : null }}">
      <span class="input-group-btn">
        <input class="btn btn-success" style="width: 250px" type="submit" type="submit" id="btnMenu" onclick="change()" value=" Начать анализ">
</span>
</div>

</form> 
<br>




<?php if(isset($array)) {
echo "<table border=0><tr><td>Всего в группе: </td><td> ".$people.'&nbsp;&nbsp;&nbsp;</td><td></td></tr>' .
 "<tr><td> Из них собачек: </td><td>" . round(($ban_count / $people ) * 100) . '% &nbsp;&nbsp;&nbsp; </td><td> (' .$ban_count .' человек) </td></tr>'.
 "<tr><td> Мужчин: </td><td>" . round(($mans / $people ) * 100) . '%</td><td> ('. $mans . ' человек)</td></tr>'.
 "<tr><td> Женщин: </td><td>". round(($womens / $people ) * 100) . '%</td><td> (' . $womens . ' человек)</td></tr>'.
 "<tr><td> С закрытой личкой:  &nbsp;&nbsp;&nbsp;</td><td>" . round(($ls_close / $people ) * 100) . '%</td><td> ('  . $ls_close . ' человек)</td></tr></table>'.

 "<br><br>Статистика по возрасту: <br><br><table>" .
"<tr><td>0-14 лет: </td><td>"  . round((count($array_age['0-14']) / $people ) * 100). '% &nbsp;&nbsp;&nbsp;</td><td> ('. count($array_age['0-14'])  . ' человек) </td></tr>' .
"<tr><td>15-24 лет: </td><td>"  . round((count($array_age['15-24']) / $people ) * 100) . '%</td><td> ('. count($array_age['15-24']) . ' человек)</td></tr>' .
"<tr><td>25-34 лет: </td><td>"  . round((count($array_age['25-34']) / $people ) * 100) . '%</td><td> ('. count($array_age['25-34']) . ' человек)</td></tr>' .
"<tr><td>35-44 лет: </td><td>" . round((count($array_age['35-44']) / $people ) * 100) . '%</td><td> (' . count($array_age['35-44']) . ' человек)</td></tr>' .
"<tr><td>45-54 лет: </td><td>" . round((count($array_age['45-54']) / $people ) * 100) . '%</td><td> ('. count($array_age['45-54']) . ' человек)</td></tr>' .
"<tr><td>55-64 лет: </td><td>" . round((count($array_age['55-64']) / $people ) * 100) . '%</td><td> ('. count($array_age['55-64']) . ' человек)</td></tr>' .
"<tr><td>65 и больше: &nbsp;&nbsp;&nbsp;</td><td>". round((count($array_age['65']) / $people ) * 100) . '%</td><td> ('.count($array_age['65']) .' человек)</td></tr></table>';
		


echo "<br><br> Статистика по семейному положению: <br><br><table>" .
	 "<tr><td>Не женат, не замужем: </td><td>" . round((count($array['1']) / $people ) * 100) .'%</td><td> (' . count($array['1']) . ' человек)</td></tr>' .
	 "<tr><td> есть друг/есть подруга: </td><td>" . round((count($array['2']) / $people ) * 100).'%</td><td> (' .  count($array['2']). ' человек)</td></tr>' .
	 "<tr><td>Помолвлен/помолвлена: &nbsp;&nbsp;&nbsp;</td><td>" . round((count($array['3']) / $people ) * 100).'% &nbsp;&nbsp;&nbsp;</td><td> (' .  count($array['3']). ' человек)</td></tr>' .
	 "<tr><td>Женат/замужем: </td><td>" . round((count($array['4']) / $people ) * 100).'%</td><td> (' .  count($array['4']). ' человек)</td></tr>' .
	 "<tr><td>Всё сложно: </td><td>" . round((count($array['5']) / $people ) * 100).'%</td><td> (' .  count($array['5']). ' человек)</td></tr>' .
	 "<tr><td>В активном поиске: </td><td>" . round((count($array['6']) / $people ) * 100).'%</td><td> (' .  count($array['6']). ' человек)</td></tr>' .
	 "<tr><td>Влюблён/влюблена: </td><td>" . round((count($array['7']) / $people ) * 100).'%</td><td> (' .  count($array['7']). ' человек)</td></tr>' .
	 "<tr><td>В гражданском браке: </td><td>" . round(($array['8'] / $people ) * 100).'%</td><td> (' .  $array['8']. ' человек)</td></tr>' .
	 "<tr><td>Не указано: </td><td>" . round((count($array['0']) / $people ) * 100).'%</td><td> (' .  count($array['0']). ' человек)</td></tr></table>';
} 
?>



@endsection