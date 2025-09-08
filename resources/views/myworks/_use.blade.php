
    <div class="dropdown">
        <button class="submitbutton right dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            Использовать результат
        </button>
            
    <ul class="dropdown-menu " aria-labelledby="dropdownMenuButton1">
    
    @if($item->count == '0')
        <li class="ShowGroupDesc"> < недоступно ></li>
    @else

        @if($item->WorkType->type_desc == 'users')

        <li class="drop-menu item-drop-menu"><a href="{{route('tool1', ['id' => $item->id])}}" class="dropdown-item">Отобразить профили</a></li>
        <li class="drop-menu item-drop-menu"> <a href="{{route('usersfilter', ['id' => $item->id])}}" class="dropdown-item">Отфильтровать пользователей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('socialnetworks', ['id' => $item->id])}}" class="dropdown-item">Собрать социальные сети (inst, skype и тд)</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getfriends', ['id' => $item->id])}}" class="dropdown-item">Собрать друзей и подписчиков</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getrelatives', ['id' => $item->id])}}" class="dropdown-item">Собрать вторых половинок и родственников</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('opinionliders', ['id' => $item->id])}}" class="dropdown-item">Найти лидеров мнений</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getactivityuser', ['id' => $item->id])}}" class="dropdown-item">Собрать активность со страниц</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('NewFriendsCreate', ['id' => $item->id])}}" class="dropdown-item">Мониторинг новых друзей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('usersallgroups', ['id' => $item->id])}}" class="dropdown-item">Собрать все сообщества пользователей</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('usersgroups', ['id' => $item->id])}}" class="dropdown-item">Собрать группы где сидит эта ЦА</a></li>

        @elseif($item->WorkType->type_desc == 'groups')

        <li class="drop-menu item-drop-menu"><a href="{{route('showgroups', ['id' => $item->id])}}" class="dropdown-item">Отобразить группы</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('filtergroups', ['id' => $item->id])}}" class="dropdown-item">Отфильтровать группы</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getallmembers', ['id' => $item->id])}}" class="dropdown-item">Собрать участников групп</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getactivitygroups', ['id' => $item->id])}}" class="dropdown-item">Собрать активность в группах</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getpromoposts', ['id' => $item->id])}}" class="dropdown-item">Найти промо-посты группах</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('getgroupcontacts', ['id' => $item->id])}}" class="dropdown-item">Собрать администраторов в группах</a></li>
        <li class="drop-menu item-drop-menu"><a href="{{route('NewMembersCreate', ['id' => $item->id])}}" class="dropdown-item">Мониторинг новых вступлений в группы</a></li>
        
        @elseif($item->WorkType->type_desc == 'posts')

        <li class="drop-menu item-drop-menu"><a href="{{route('showposts', ['id' => $item->id])}}" class="dropdown-item">Отобразить посты</a></li>
        @if($item->WorkType->type != 'GetPromoPosts')
        <li class="drop-menu item-drop-menu"><a href="{{route('getactivityposts', ['id' => $item->id])}}" class="dropdown-item">Собрать активность в постах</a></li>
        @endif
        @elseif($item->WorkType->type_desc == 'socialnetworks')
            <li class="ShowGroupDesc"> < недоступно ></li>
        @endif

    @endif


</ul>
</div>

