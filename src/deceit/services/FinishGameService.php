<?php


namespace deceit\services;


use deceit\dao\PlayerDataDAO;
use deceit\models\PlayerData;
use deceit\storages\PlayerStatusStorage;
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

        GameStorage::delete($gameId);
    }
}