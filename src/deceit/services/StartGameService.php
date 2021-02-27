<?php


namespace deceit\services;


use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\GameId;
use deceit\storages\GameStorage;

class StartGameService
{
    static function execute(string $ownerName, GameId $gameId): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;
        if ($game->getGameOwnerName() !== $ownerName) return false;
        if (count($game->getWolfNameList()) === 0) return false;

        $game->start();
        return true;
    }
}