<?php


namespace deceit\services;


use deceit\models\GameId;
use deceit\storages\GameStorage;

class FinishGameService
{
    static function execute(GameId $gameId): void {
        $game = GameStorage::findById($gameId);
        if ($game === null) return;
        $game->finish();

        GameStorage::delete($gameId);
    }
}