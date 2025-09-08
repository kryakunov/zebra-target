

<label class='label'>Введите имя и фамилию:</label><br>
<input type="text"  id="finduser"> <a class="noborder" onclick="getuser()">Поиск</a>
<br><br>
<span id="user"></span>

<script type="text/javascript">

  function getuser() 
  {
      var text = document.getElementById("finduser");

      $.ajax({
          url: 'ajaxgetuser/'+text.value,
          
          success: function(data) {

            //result.innerHTML = data;
             var res = JSON.parse(data);
             
             var _datalist = document.getElementById("user");

             text = '<input type=hidden value=' + res.vk_id + '><img src=' + res.photo + ' class=circle> ' + res.first_name + ' ' + res.last_name;
             _datalist.innerHTML = text;
          }
      });
  };

  </script>


