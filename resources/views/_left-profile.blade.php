

@if(session('access') > '4070900800')
<div class="zoloto">
  <img src="{{session('photo')}}" class="circle" style="float:left; margin-right: 5px;" width="45" height="45">
  <b>{{session('first_name')}} {{session('last_name')}}</b><br>
  <span class="eternal_access">Вечный доступ</span>
</div>
@else

<div class="profile-left">
      <a href="/price">
        <img src="{{session('photo')}}" class="circle" style="float:left; margin-right: 5px;" width="45" height="45">
        <b>{{session('first_name')}} {{session('last_name')}}</b><br>
        @if(session('access') > time())
          <span id="profile-access">Полный доступ ещё {{ round((session('access') - time()) / 86400) }} дней </span>
        @else
          <span id="profile-access">У вас бесплатный доступ</span>
        @endif
      </a>
</div>
@endif


@if(count($myworks) > 0)
<div class="profile-left">
      <a href="/profile">
        <p class='workTitle'>Последние задачи</p>

          @foreach($myworks as $value)
          <p class='workName'>
          <!--<img src="https://zebra-target.ru/PNG/{{ ($value['status']) ? 'icon_check.png' : 'icon_hourglass.png' }}" width="16" height="16">-->
            {{ mb_strcut($value['name'], 0, 50) }}...

            <span class=workPercent><span id="percent-{{$value['id']}}">{{$value['percent']}}%
              @php
               // echo ($value['percent'] == 100) ? '<span class=workPercent>100%</span>' : '<b>'.$value['percent'].'%</b>';
              @endphp
            </span></span>

          </p>
          @endforeach
      </a>
</div>


<script type="text/javascript">

    function modeleft()
    {

        $.ajax({

            url: 'ajaxleft',
            success: function(data) {

                var res = JSON.parse(data);

                res.forEach(function(item, index, res){

                    var p = document.getElementById('percent-'+item.id);

                    p.innerHTML = item.percent+'%';

                });
            }
        });
    };


    setInterval(modeleft, 3000);


</script>

@endif
