<?php


namespace deceit\services;


use deceit\dao\PlayerDataDAO;
use deceit\data\PlayerData;
use deceit\pmmp\events\UpdatedGameDataEvent;
use deceit\storages\GameStorage;
use deceit\storages\PlayerStatusStorage;

class QuitGameService
{
    static function execute(string $playerName): bool {
        $playerData = PlayerDataDAO::findByName($playerName);
        $belongGameId = $playerData->getBelongGameId();
        if ($belongGameId === null) return false;

        PlayerDataDAO::update(new PlayerData($playerName));
        $game = GameStorage::findById($belongGameId);
        $game->removePlayer($playerName);

        PlayerStatusStorage::delete($playerName);

        $event = new UpdatedGameDataEvent($belongGameId);
        $event->call();
        return true;
    }
}