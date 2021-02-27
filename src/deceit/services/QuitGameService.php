<?php


namespace deceit\services;


use deceit\dao\PlayerStatusDAO;
use deceit\models\PlayerStatus;
use deceit\storages\GameStorage;
use deceit\storages\PlayerDataOnGameStorage;

class QuitGameService
{
    static function execute(string $playerName): bool {
        $status = PlayerStatusDAO::findByName($playerName);
        $belongGameId = $status->getBelongGameId();
        if ($belongGameId === null) return false;

        PlayerStatusDAO::update(new PlayerStatus($playerName));
        $game = GameStorage::findById($belongGameId);
        $game->removePlayer($playerName);

        PlayerDataOnGameStorage::delete($playerName);
        return true;
    }
}