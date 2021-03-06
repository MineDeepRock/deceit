<?php


namespace deceit;


class DataFolderPath
{
    static string $map;
    static string $playerData;
    static string $skin;
    static string $geometry;

    static function init(string $dataPath,string $resourcePath) {
        self::$map = $dataPath . "maps/";
        if (!file_exists(self::$map)) mkdir(self::$map);

        self::$playerData = $dataPath . "player_data/";
        if (!file_exists(self::$playerData)) mkdir(self::$playerData);

        self::$skin = $resourcePath . "skin/";
        if (!file_exists(self::$skin)) mkdir(self::$skin);

        self::$geometry = $resourcePath . "geometry/";
        if (!file_exists(self::$geometry)) mkdir(self::$geometry);
    }
}