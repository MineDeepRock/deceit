<?php


namespace deceit\services;


use deceit\models\GameId;
use deceit\storages\GameStorage;

class JoinGameService
{
    static function execute(GameId $gameId, string $playerName): void {
        $game = GameStorage::findById($gameId);
        if ($game !== null) {
            $game->addPlayer($playerName);
        }
    }
}