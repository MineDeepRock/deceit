<?php


namespace deceit\pmmp\services;


use deceit\models\Map;
use deceit\pmmp\entities\BloodPackEntity;
use pocketmine\Server;

class SpawnBloodPackPMMPService
{
    static function execute(Map $map): void {
        $level = Server::getInstance()->getLevelByName($map->getLevelName());
        if ($level === null) return;

        foreach ($map->getBloodPackSpawnVectorList() as $vector3) {
            $entity = new BloodPackEntity($level, $vector3);
            $entity->spawnToAll();
        }
    }
}