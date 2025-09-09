
@extends('layout')

@section('title') @parent Отобразить сообщества @endsection

@section('content')
<div class="tab">
<div class="title">Отобразить сообщества</div>
<div class="content">
@include('_ScriptDesk')

@include('errors.exceptions')
@include('errors.session')

@if(isset($work))
	@include('_work')
@else

<form action="{{route('showgroups')}}" method="post" class="mb-4">
@csrf


<input type="hidden" name="parentId" value="{{ (isset($_GET['id'])) ? $_GET['id'] : '' }}">


<label class='label'>Вставьте список сообществ:</label>
    <textarea class="output-panel form-control"  name="groups" rows="5" placeholder="По одному ID в строке"><?php
	if (isset($request['groups'])) echo $request['groups']; ?></textarea>
        @error('users')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

<br>


<label class='label'><b>Что показать?</b></label>
<div class="filtr_param">
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="status" value="1" <?php if (!isset($request['status']) or ($request['status'] == '1')) echo 'checked'; ?>></input> Статус сообщества</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="site" value="1" <?php if (!isset($request['site']) or ($request['site'] == '1')) echo 'checked'; ?>></input> Веб-сайт</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="city" value="1" <?php if (!isset($request['city']) or ($request['city'] == '1')) echo 'checked'; ?>></input> Город</label><Br>
	<label class='label-checkbox'><input type="checkbox" class='checkbox' name="members" value="1" <?php if (!isset($request['members']) or ($request['members'] == '1')) echo 'checked'; ?>></input> Количество подписчиков</label><Br>
</div>
<br>
<input class="btn btn-success btn_size" type="submit" id="btnMenu" value=" Показать "  onclick="change()">


</form>

@endif


@if(isset($data))
<table class='table'>
<thead class='table-head'>
	<tr>
		<th>ID</th>
		<th colspan='2'>Сообщество</th>
		<?php  if (isset($request['members'])) echo "<th>Подписчики</th>"; ?>
		<?php  if (isset($request['site'])) echo "<th>Сайт</th>"; ?>
		<?php  if (isset($request['city'])) echo "<th>Город</th>"; ?>
	</tr>
</thead>
<tbody>
@endif

<?php

if (isset($data))
 foreach($data as $value):

	if(!isset($value['members_count'])) continue;
if (isset($request['members']))
{
if (strlen($value['members_count']) == 6)
	$value['members_count'] = chunk_split($value['members_count'],3," ");
elseif(strlen($value['members_count']) == 7)
	$value['members_count'] = substr($value['members_count'], 0, 1).' '.substr($value['members_count'], 1, 3).' '.substr($value['members_count'], 4, 7);
elseif(strlen($value['members_count']) == 8)
	$value['members_count'] = substr($value['members_count'], 0, 2).' '.substr($value['members_count'], 2, 3).' '.substr($value['members_count'], 5, 8);
}


if (!empty($value['site'])) {
	$pos = strpos($value['site'], 'http');
	if ($pos === false) {
		$value['site'] = 'https://' . $value['site'];
}
	$value['site'] = "<a href=".$value['site']." target=_blank>site</a>";
}
	else $value['site'] = '';

if (!empty($value['city']['title'])) $value['city']['title'] = "<span class='ShowGroupAbout'>".$value['city']['title']."</span";
	else $value['city']['title'] = '-';
?>

	<tr>
		<td class="align-middle ShowGroupDesc" width="10"><?=$value['id']?></td>
		<td class="ShowGroupDesc" width="10"><img class='circle' width='50' height='50' src='<?=$value['photo_50']?>'></td>
		<td class=""><a href='https://vk.com/club<?=$value['id']?>' target='_blank' class='ShowGroupName'><?=$value['name']?></a>
			<?php if (isset($request['status']))
				echo "<br><span class='ShowGroupDesc'>".$value['status']."</span>"; ?>
		</td>
		<?php  if (isset($request['members'])) echo "<td class='ShowGroupDesc'>".$value['members_count']."</td>"; ?>
		<?php  if (isset($request['site'])) echo "<td class='ShowGroupDesc'>".$value['site']."</td>"; ?>
		<?php  if (isset($request['city'])) echo "<td class='ShowGroupDesc'>".$value['city']['title']."</td>"; ?>
	</tr>


<?php

endforeach;


?>

</tbody>
</table>

</div>
</div>





@endsection
