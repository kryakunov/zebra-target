
    <div class="dropdown">
        <button class="submitbutton right dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Использовать результат
        </button>
            
    <ul class="dropdown-menu " aria-labelledby="dropdownMenuButton1">
    
    @if($item->count == '0')
        <li class="ShowGroupDesc"> < недоступно ></li>
    @else

        @if($item->WorkType->type_desc == 'users')

        <li class="drop-menu item-drop-menu"><a href="{{route('tool1', ['cloud_id' => $item->id])}}" class="dropdown-item">Отобразить профили</a></li>
        <li class="drop-menu item-drop-menu"> <a href="{{route('usersfilter', ['cloud_id' => $item->id])}}" class="dropdown-item">Отфильтровать пользователей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('socialnetworks', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать социальные сети</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getfriends', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать друзей и подписчиков</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('opinionliders', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать лидеров мнений</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getactivityuser', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать активность со страниц</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('NewFriendsCreate', ['cloud_id' => $item->id])}}" class="dropdown-item">Мониторинг новых друзей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('usersgroups', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать популярные сообщества где сидит эта ЦА</a></li>

        @elseif($item->WorkType->type_desc == 'groups')

        <li class="drop-menu item-drop-menu"><a href="{{route('showgroups', ['cloud_id' => $item->id])}}" class="dropdown-item">Отобразить группы</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('filtergroups', ['cloud_id' => $item->id])}}" class="dropdown-item">Отфильтровать группы</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getallmembers', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать участников групп</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getactivitygroups', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать активность в группах</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getgroupcontacts', ['cloud_id' => $item->id])}}" class="dropdown-item">Собрать администраторов в группах</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('NewMembersCreate', ['cloud_id' => $item->id])}}" class="dropdown-item">Мониторинг новых вступлений в группы</a></li>
        
        @elseif($item->WorkType->type_desc == 'posts')

        <li class="drop-menu item-drop-menu"><a href="{{route('getactivityposts', ['id' => $item->id])}}" class="dropdown-item">Собрать активность в постах</a></li>

        @elseif($item->WorkType->type_desc == 'socialnetworks')
            <li class="ShowGroupDesc"> < недоступно ></li>
        @endif

    @endif


</ul>
</div>

