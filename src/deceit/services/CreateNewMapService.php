<?php


namespace deceit\services;


use deceit\dao\MapDAO;
use deceit\models\Map;
use pocketmine\block\BlockIds;
use pocketmine\math\Vector3;

class CreateNewMapService
{
    static function execute(string $levelName, string $mapName, Vector3 $spawnPoint): bool {
        if ($levelName === null or empty($mapName)) return false;

        $newMap = new Map(
            $levelName,
            $mapName,
            $spawnPoint,
            $spawnPoint,
            BlockIds::STONE,
            [],
            [],
            [],
            [],
            [],
        );

        MapDAO::save($newMap);

        return true;
    }
}