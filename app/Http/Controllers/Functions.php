<?php

namespace App\Http\Controllers\Functions;

class Functions
{

    public static function clearGroupName($name)
    {
        $delete = [
            "/club",
            "vk.ru",
            "http://",
            "https://",
            "/public",
            " ",
            "/"];

        $replace = "";

        // Делаем проверку на массив
        if (is_array($name))
        {
            $name = array_diff($name, array('',' '));
            $count = count($name);
            for($i = 0; $i < $count; $i++)
            {
                $name[$i] = str_replace($delete, $replace, $name[$i]);
                $name[$i] = trim($name[$i]);
                // Удаляем пустые элементы массива
                $name = array_diff($name, array('',' '));
            }

            return $name;
        }

        $name = str_replace($delete, $replace, $name);
        $name = trim($name);

        return $name;
    }
}

