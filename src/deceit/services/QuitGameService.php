<?php


namespace deceit\services;


use deceit\dao\PlayerDataDAO;
use deceit\models\PlayerData;
use deceit\pmmp\events\UpdatedGameDataEvent;
use deceit\storages\GameStorage;
use deceit\storages\PlayerDataOnGameStorage;

class QuitGameService
{
    static function execute(string $playerName): bool {
        $playerData = PlayerDataDAO::findByName($playerName);
        $belongGameId = $playerData->getBelongGameId();
        if ($belongGameId === null) return false;

        PlayerDataDAO::update(new PlayerData($playerName));
        $game = GameStorage::findById($belongGameId);
        $game->removePlayer($playerName);

        PlayerDataOnGameStorage::delete($playerName);

        $event = new UpdatedGameDataEvent($belongGameId);
        $event->call();
        return true;
    }
}