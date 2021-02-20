<?php


namespace deceit\services;


use deceit\dao\PlayerStatusDAO;
use deceit\storages\GameStorage;

class QuitGameService
{
    static function execute(string $playerName): void {
        $status = PlayerStatusDAO::findByName($playerName);
        $belongGameId = $status->getBelongGameId();
        if ($belongGameId === null) return;

        $game = GameStorage::findById($belongGameId);
        if ($game === null) return;

        $game->removePlayer($playerName);
    }
}