<?php


namespace deceit;


class DataFolderPath
{
    static string $map;
    static string $playerStatus;
    static string $skin;
    static string $geometry;

    static function init(string $dataPath,string $resourcePath) {
        self::$map = $dataPath . "maps/";
        if (!file_exists(self::$map)) mkdir(self::$map);

        self::$playerStatus = $dataPath . "player_status/";
        if (!file_exists(self::$playerStatus)) mkdir(self::$playerStatus);

        self::$skin = $resourcePath . "skin/";
        if (!file_exists(self::$skin)) mkdir(self::$skin);

        self::$geometry = $resourcePath . "geometry/";
        if (!file_exists(self::$geometry)) mkdir(self::$geometry);
    }
}