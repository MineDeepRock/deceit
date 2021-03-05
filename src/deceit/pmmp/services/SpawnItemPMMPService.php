<?php


namespace deceit\pmmp\services;


use deceit\models\Map;
use pocketmine\Server;

class SpawnItemPMMPService
{
    //TODO:ランダムに
    static function execute(Map $map): void {
        $level = Server::getInstance()->getLevelByName($map->getLevelName());
        if ($level === null) return;

        foreach ($map->getItemDataOnMapList() as $itemDataOnMap) {
            $entity = $itemDataOnMap->getAsEntity($level);
            $entity->spawnToAll();
        }

        foreach ($map->getGunDataOnMapList() as $gunDataOnMap) {
            $entity = $gunDataOnMap->getAsEntity($level);
            $entity->spawnToAll();
        }
    }
}