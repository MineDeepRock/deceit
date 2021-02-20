<?php


namespace deceit;


class DataFolderPath
{
    static string $map;

    static function init(string $path) {
        self::$map = $path . "maps/";

        if (!file_exists(self::$map)) mkdir(self::$map);
    }
}