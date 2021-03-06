<?php


namespace deceit\pmmp\entities;


use deceit\dao\PlayerDataDAO;
use deceit\storages\GameStorage;
use pocketmine\Player;

class BloodPackEntity extends EntityBase
{
    const NAME = "BloodEntity";

    public function onAttackedByPlayer(Player $player): void {
        $playerData = PlayerDataDAO::findByName($player->getName());
        $game = GameStorage::findById($playerData->getBelongGameId());
        if ($game === null) return;
        if (!$game->isStarted() or $game->isFinished()) return;

        if (in_array($player->getName(),$game->getWolfNameList())) {

            //TODO : スキン変更
        }
    }
}