
   <br>
<label class='label'>Подгрузить из моих баз</label>
  <select class="form-control" name="cloud">
    <option>- выбрать -</option>
    <?php 
      $result = json_decode(file_get_contents('http://zebra-target.ru/ajaxgetcloud?id='.session('id')), true);
 
      if ($result)
      foreach($result as $value){
        echo '<option value='.$value['id'].'>'.$value['name'].'</option>';
      }

?>
   </select>
   <br><br>
<!--
<label class=control-label>Выберите страну</label>

<select class="form-control" name="county">
  <option value="1">Россия</option>
  <option value="2">Украина</option>
  <option value="3">Беларусь</option>
</select>


<br>

<label class=control-label>Введите город</label>

<div class="row" id="formm">



<input type="text" class="form-control" name="city" id="city" list="cities"> 
  <datalist id="cities">
    <option value="Москва">
		<option value="Санкт-Петербург">
		<option value="Казань">
		<option value="Краснодар">
		<option value="Екатеринбург">
		<option value="Новосибирск">
		<option value="Самара">
		<option value="Волгоград">
  </datalist>

</div>
<div class="row">
<div class="col-md-5 mt-1">



<button type="button" class="btn btn-outline-info btn-sm">екб
<span class="d-none" id="delBtn">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
  </svg>
</span>
<input type="hidden" name="city" value="ekb">
</button>

<button type="button" class="btn btn-outline-info btn-sm">питер
<span class="d-none" id="delBtn">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
  </svg>
</span>
<input type="hidden" name="city" value="spb">
</button>



<button type="button" class="btn btn-outline-info btn-sm">мск
<span class="d-none" id="delBtn">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
  </svg>
</span>
<input type="hidden" name="city" value="msk">
</button>

<div class="test">.</div>

</div>
</div>

    -->




<script type="text/javascript">

$(".btn-outline-info").hover(
  function() {
   $("span#delBtn", this).addClass('d-inblock').removeClass('d-none');
  },
  function() {
   $("span#delBtn", this).addClass('d-none').removeClass('d-inblock');
   $(this).addClass('btn-outline-info').removeClass('btn-outline-warning');
  },
);
$("span#delBtn").bind( 'click' ,
  function() {
    if($(this).parent('button').hasClass('btn-outline-warning')){
        $(this).parent('button').remove();
    }else {
        $(this).parent('button').addClass('btn-outline-warning').removeClass('btn-outline-info');
    }
  }
);

</script>


<script type="text/javascript">
  function mode(text) 
  {
      $.ajax({
          url: 'ajaxgetcity?city=' + text,
          
          success: function(data) {
            //result.innerHTML = data;
             var res = JSON.parse(data);

             var _datalist = document.getElementById("test");
             var _option = "";

             $i = 0;
              res.forEach(function(item, index, res){
 
                _option += "<option value='" + i++ + "' />test">";
                _datalist.innerHTML = _option;
             });
          }
      });
  };



  </script>


