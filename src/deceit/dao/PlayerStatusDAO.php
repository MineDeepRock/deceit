<?php


namespace deceit\dao;


use deceit\dto\PlayerStatusDTO;
use deceit\DataFolderPath;
use deceit\models\PlayerStatus;

class PlayerStatusDAO
{
    static function findByName(string $name): ?PlayerStatus {
        if (!file_exists(DataFolderPath::$playerStatus . $name . ".json")) return null;

        $playerData = json_decode(file_get_contents(DataFolderPath::$playerStatus . $name . ".json"), true);
        return PlayerStatusDTO::decode($playerData);
    }

    static function all(): array {
        $playerStatusList = [];
        $dh = opendir(DataFolderPath::$playerStatus);
        while (($fileName = readdir($dh)) !== false) {
            if (filetype(DataFolderPath::$playerStatus . $fileName) === "file") {
                $data = json_decode(file_get_contents(DataFolderPath::$playerStatus . $fileName), true);
                $playerStatusList[] = PlayerStatusDTO::decode($data);
            }
        }

        closedir($dh);

        return $playerStatusList;
    }

    static function save(PlayerStatus $playerStatus): void {
        if (self::findByName($playerStatus->getName()) !== null) return;

        file_put_contents(DataFolderPath::$playerStatus . $playerStatus->getName() . ".json", json_encode(PlayerStatusDTO::encode($playerStatus)));
    }

    static function update(PlayerStatus $playerStatus): void {
        if (self::findByName($playerStatus->getName()) === null) return;

        file_put_contents(DataFolderPath::$playerStatus . $playerStatus->getName() . ".json", json_encode(PlayerStatusDTO::encode($playerStatus)));
    }
}