<?php


namespace deceit\pmmp\services;


use deceit\DataFolderPath;
use deceit\pmmp\PlayerInventoryContentsStorage;
use deceit\pmmp\scoreboards\OnGameScoreboard;
use deceit\storages\GameStorage;
use deceit\storages\PlayerStatusStorage;
use pocketmine\entity\Attribute;
use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\Server;

class TransformToWolfPMMPService
{
    static function execute(Player $player): void {
        $playerStatus = PlayerStatusStorage::findByName($player->getName());
        if ($playerStatus === null) return;
        if ($playerStatus->canTransform()) {
            $game = GameStorage::findById($playerStatus->getBelongGameId());
            if ($game === null) return;//TODO:エラー

            $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(0.30);
            $player->setScale(1.3);
            $player->setSkin(new Skin("Standard_CustomSlim", file_get_contents(DataFolderPath::$skin . "wolf.skin")));
            $player->sendSkin();
            $playerStatus->startTransformTimer();

            foreach ($game->getWolfNameList() as $wolfName) {
                $wolfPlayer = Server::getInstance()->getPlayer($wolfName);
                OnGameScoreboard::update($wolfPlayer, $game);
            }

            //インベントリを保存しクリア
            PlayerInventoryContentsStorage::save($player->getName(), $player->getInventory()->getContents());
            $player->getInventory()->setContents([]);
        }
    }
}