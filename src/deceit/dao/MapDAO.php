<?php

namespace deceit\dao;


use deceit\adapters\MapJsonAdapter;
use deceit\DataFolderPath;
use deceit\models\Map;

class MapDAO
{
    static function findByName(string $name): ?Map {
        if (!file_exists(DataFolderPath::$map . $name . ".json")) return null;

        $mapsData = json_decode(file_get_contents(DataFolderPath::$map . $name . ".json"), true);
        return MapJsonAdapter::decode($mapsData);
    }

    /**
     * @return Map[]
     */
    static function all(): array {
        $maps = [];
        $dh = opendir(DataFolderPath::$map);
        while (($fileName = readdir($dh)) !== false) {
            if (filetype(DataFolderPath::$map . $fileName) === "file") {
                $data = json_decode(file_get_contents(DataFolderPath::$map . $fileName), true);
                $maps[] = MapJsonAdapter::decode($data);
            }
        }

        closedir($dh);

        return $maps;
    }

    static function save(Map $map): void {
        if (self::findByName($map->getName()) !== null) return;

        file_put_contents(DataFolderPath::$map . $map->getName() . ".json", json_encode(MapJsonAdapter::encode($map)));
    }

    static function update(Map $map): void {
        if (self::findByName($map->getName()) === null) return;

        file_put_contents(DataFolderPath::$map . $map->getName() . ".json", json_encode(MapJsonAdapter::encode($map)));
    }

    static function delete(string $mapName): void {
        unlink(DataFolderPath::$map . $mapName . ".json");
    }
}