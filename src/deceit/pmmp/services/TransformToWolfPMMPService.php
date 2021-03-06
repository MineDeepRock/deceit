<?php


namespace deceit\pmmp\services;


use deceit\DataFolderPath;
use deceit\storages\PlayerStatusStorage;
use pocketmine\entity\Attribute;
use pocketmine\entity\Skin;
use pocketmine\Player;

class TransformToWolfPMMPService
{
    static function execute(Player $player): void {
        $playerStatus = PlayerStatusStorage::findByName($player->getName());
        if ($playerStatus === null) return;
        if ($playerStatus->canTransform()) {
            $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(1.3);
            $player->setScale(1.3);
            $player->setSkin(new Skin("Standard_CustomSlim", file_get_contents(DataFolderPath::$skin ."wolf.skin")));
            $playerStatus->startTransformTimer();

            //TODO:インベントリを一旦保存しクリア
        }
    }
}