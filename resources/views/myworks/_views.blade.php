@if($item['share'] == 1)
    <div class="share-views">
        <img src="https://zebra-target.ru/PNG/icon_circle-slelected.png" width="16" height="16"> 
        <span class="time">{{ ($item->views > 0) ? $item->views : '0' }} просмотров</span>
<br>
        <label class='label'>Ссылка на эту задачу: </label>
            <input type="text" readonly class="input-share" value="https://zebra-target.ru/works/{{$item->id}}">
        </label>
        
    </div>
@endif 