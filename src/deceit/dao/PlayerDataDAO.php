<?php


namespace deceit\dao;


use deceit\dto\PlayerDataDTO;
use deceit\DataFolderPath;
use deceit\models\PlayerData;

class PlayerDataDAO
{
    static function findByName(string $name): ?PlayerData {
        if (!file_exists(DataFolderPath::$playerData . $name . ".json")) return null;

        $playerData = json_decode(file_get_contents(DataFolderPath::$playerData . $name . ".json"), true);
        return PlayerDataDTO::decode($playerData);
    }

    static function all(): array {
        $playerDataList = [];
        $dh = opendir(DataFolderPath::$playerData);
        while (($fileName = readdir($dh)) !== false) {
            if (filetype(DataFolderPath::$playerData . $fileName) === "file") {
                $data = json_decode(file_get_contents(DataFolderPath::$playerData . $fileName), true);
                $playerDataList[] = PlayerDataDTO::decode($data);
            }
        }

        closedir($dh);

        return $playerDataList;
    }

    static function save(PlayerData $playerData): void {
        if (self::findByName($playerData->getName()) !== null) return;

        file_put_contents(DataFolderPath::$playerData . $playerData->getName() . ".json", json_encode(PlayerDataDTO::encode($playerData)));
    }

    static function update(PlayerData $playerData): void {
        if (self::findByName($playerData->getName()) === null) return;

        file_put_contents(DataFolderPath::$playerData . $playerData->getName() . ".json", json_encode(PlayerDataDTO::encode($playerData)));
    }
}