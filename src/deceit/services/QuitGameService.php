<?php


namespace deceit\services;


use deceit\dao\PlayerStatusDAO;
use deceit\storages\GameStorage;
use deceit\storages\PlayerDataOnGameStorage;

class QuitGameService
{
    static function execute(string $playerName): bool {
        $status = PlayerStatusDAO::findByName($playerName);
        $belongGameId = $status->getBelongGameId();
        $game = GameStorage::findById($belongGameId);
        if ($game === null) return false;

        PlayerDataOnGameStorage::delete($playerName);
        $game->removePlayer($playerName);
        return true;
    }
}