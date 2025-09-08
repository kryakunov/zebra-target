<?php

function openFile($hash)
{
    $path = '../storage/app/liders/' . $hash;
    $file = file_get_contents($path);
    $file = explode("\n", $file);

    return $file;
}