<?php

namespace deceit\dao;


use deceit\dto\MapDTO;
use deceit\DataFolderPath;
use deceit\models\Map;

class MapDAO
{
    static function findByName(string $name): ?Map {
        if (!file_exists(DataFolderPath::$map . $name . ".json")) return null;

        $mapsData = json_decode(file_get_contents(DataFolderPath::$map . $name . ".json"), true);
        return MapDTO::decode($mapsData);
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
                $maps[] = MapDTO::decode($data);
            }
        }

        closedir($dh);

        return $maps;
    }

    static function save(Map $map): void {
        if (self::findByName($map->getName()) !== null) return;

        file_put_contents(DataFolderPath::$map . $map->getName() . ".json", json_encode(MapDTO::encode($map)));
    }

    static function update(string $mapName, Map $map): void {
        if (self::findByName($map->getName()) === null) return;
        if ($mapName !== $map->getName()) self::delete($mapName);

        file_put_contents(DataFolderPath::$map . $map->getName() . ".json", json_encode(MapDTO::encode($map)));
    }

    static function delete(string $mapName): void {
        unlink(DataFolderPath::$map . $mapName . ".json");
    }
}