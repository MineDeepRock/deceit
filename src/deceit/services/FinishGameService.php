<?php


namespace deceit\services;


use deceit\dao\PlayerDataDAO;
use deceit\data\PlayerData;
use deceit\storages\PlayerStatusStorage;
use deceit\storages\WaitingRoomStorage;
use deceit\types\GameId;
use deceit\storages\GameStorage;

class FinishGameService
{
    static function execute(GameId $gameId): void {
        $game = GameStorage::findById($gameId);
        if ($game === null) return;

        foreach ($game->getPlayerNameList() as $playerName) {
            PlayerDataDAO::update(new PlayerData($playerName));
            PlayerStatusStorage::delete($playerName);
        }

        $game->finish();
        WaitingRoomStorage::returnWaitingRoom($game->getWaitingRoom());

        GameStorage::delete($gameId);
    }
}