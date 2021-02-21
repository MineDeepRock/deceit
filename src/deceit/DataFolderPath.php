<?php


namespace deceit;


class DataFolderPath
{
    static string $map;
    static string $playerStatus;
    static string $skin;
    static string $geometry;

    static function init(string $path) {
        self::$map = $path . "maps/";
        if (!file_exists(self::$map)) mkdir(self::$map);

        self::$playerStatus = $path . "player_status/";
        if (!file_exists(self::$playerStatus)) mkdir(self::$playerStatus);
    }
}