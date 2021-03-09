<?php


namespace deceit\pmmp\services;


use deceit\pmmp\PlayerInventoryContentsStorage;
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
        $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(0.10);
        $player->setScale(1);
        $player->setSkin(GetPlayerSkin::execute($player));
        $player->sendSkin();

        //インベントリを復元
        $contents = PlayerInventoryContentsStorage::get($player->getName());
        if ($contents === null) {
            $player->getInventory()->setContents([]);
        } else {
            $player->getInventory()->setContents($contents);
        }
    }
}