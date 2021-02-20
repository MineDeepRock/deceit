<?php


namespace deceit\services;


use deceit\dao\PlayerStatusDAO;
use deceit\storages\GameStorage;

class QuitGameService
{
    static function execute(string $playerName): bool {
        $status = PlayerStatusDAO::findByName($playerName);
        $belongGameId = $status->getBelongGameId();
        if ($belongGameId === null) return false;

        $game = GameStorage::findById($belongGameId);
        if ($game === null) return false;

        $game->removePlayer($playerName);
        return true;
    }
}