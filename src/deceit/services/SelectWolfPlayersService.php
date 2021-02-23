<?php


namespace deceit\services;


use deceit\models\GameId;
use deceit\storages\GameStorage;

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
            $wolfPlayersName[] = $game->getPlayersName()[$wolfPlayerNameIndex];
        }
        $game->setWolfNameList($wolfPlayersName);

        return true;
    }
}