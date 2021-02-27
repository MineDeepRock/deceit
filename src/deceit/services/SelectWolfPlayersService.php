<?php


namespace deceit\services;


use deceit\models\PlayerDataOnGame;
use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\GameId;
use deceit\storages\GameStorage;
use deceit\types\PlayerStateOnGame;

class SelectWolfPlayersService
{
    static function execute(GameId $gameId): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;

        $wolfPlayersName = [];
        $wolfPlayersNameIndexList = array_rand($game->getPlayerNameList(), $game->getWolfsCount());
        foreach ($wolfPlayersNameIndexList as $wolfPlayerNameIndex) {
            $wolfPlayerName = $game->getPlayerNameList()[$wolfPlayerNameIndex];
            $wolfPlayersName[] = $wolfPlayerName;

            PlayerDataOnGameStorage::update(new PlayerDataOnGame($wolfPlayerName, $gameId, PlayerStateOnGame::Alive(), true));
        }
        $game->setWolfNameList($wolfPlayersName);

        return true;
    }
}