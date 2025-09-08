@if($item->status == 1)
<div class='MyWorkFreeAccess'>
    <?php
        if (!App\Functions::isFullAccess()){
            echo "<img src='https://zebra-target.ru/PNG/icon_lock_alt.png' width='16' height='16'> ";
            if ($item->WorkType->type_desc == 'users')
                echo 'У вас бесплатный доступ. Вам доступны только 50 пользователей из ' . $item->count . ' найденных  <a href=price target=_blank class=nodecoration> Приобрести полный доступ</a>';
                if ($item->WorkType->type_desc == 'groups') echo 'У вас бесплатный доступ. Вам доступны только 10 групп из ' . $item->count . ' найденных  <a href=price target=_blank class=nodecoration> Приобрести полный доступ</a>';
                if ($item->WorkType->type_desc == 'socialnetworks') echo 'У вас бесплатный доступ. Вам доступны только 5 контактов из ' . $item->count . ' найденных  <a href=price target=_blank class=nodecoration> Приобрести полный доступ</a>';
                if ($item->WorkType->type_desc == 'posts') echo 'У вас бесплатный доступ. Вам доступны только 15 постов из ' . $item->count . ' найденных  <a href=price target=_blank class=nodecoration> Приобрести полный доступ</a>';
        }
    ?>
</div>
@endif