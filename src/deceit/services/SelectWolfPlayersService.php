<?php


namespace deceit\services;


use deceit\models\PlayerDataOnGame;
use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\GameId;
use deceit\storages\GameStorage;
use deceit\types\PlayerStateOnGame;

class SelectWolfPlayersService
{
    static function execute(string $ownerName, GameId $gameId): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;
        if ($game->getGameOwnerName() !== $ownerName) return false;

        if (count($game->getPlayersName()) <= 3) return false;
        if (count($game->getPlayersName()) - $game->getWolfsCount() * 2 <= 0) return false;

        $wolfPlayersName = [];
        $wolfPlayersNameIndexList = array_rand($game->getPlayersName(), $game->getWolfsCount());
        foreach ($wolfPlayersNameIndexList as $wolfPlayerNameIndex) {
            $wolfPlayerName = $game->getPlayersName()[$wolfPlayerNameIndex];
            $wolfPlayersName[] = $wolfPlayerName;

            PlayerDataOnGameStorage::update(new PlayerDataOnGame($wolfPlayerName, $gameId, PlayerStateOnGame::Alive(), true));
        }
        $game->setWolfNameList($wolfPlayersName);

        return true;
    }
}