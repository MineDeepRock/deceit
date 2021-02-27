<?php


namespace deceit\services;


use deceit\types\GameId;
use deceit\storages\GameStorage;

class StartGameService
{
    static function execute(string $ownerName, GameId $gameId): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;
        if ($game->getGameOwnerName() !== $ownerName) return false;

        if (count($game->getPlayerNameList()) <= 3) return false;
        if (count($game->getPlayerNameList()) - $game->getWolfsCount() * 2 <= 0) return false;

        SelectWolfPlayersService::execute($gameId);

        $game->start();
        return true;
    }
}