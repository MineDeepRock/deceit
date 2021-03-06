<?php


namespace deceit\pmmp\services;


use deceit\storages\PlayerStatusStorage;
use deceit\pmmp\utilities\GetPlayerSkin;
use pocketmine\entity\Attribute;
use pocketmine\Player;

class TransformToPlayerPMMPService
{
    static function execute(Player $player): void {
        if (!$player->isOnline()) return;
        if (!$player->isAlive()) return;

        $playerStatus = PlayerStatusStorage::findByName($player->getName());
        if ($playerStatus === null) return;
        if ($playerStatus->canTransform()) {
            $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(1);
            $player->setScale(1);
            $player->setSkin(GetPlayerSkin::execute($player));
            //TODO:インベントリを復元
        }
    }
}