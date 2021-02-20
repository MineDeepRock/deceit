<?php


namespace deceit\services;


use deceit\dao\PlayerStatusDAO;
use deceit\models\GameId;
use deceit\models\PlayerStatus;
use deceit\storages\GameStorage;

class JoinGameService
{
    static function execute(GameId $gameId, string $playerName): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;

        //Status更新
        $result = $game->addPlayer($playerName);
        if (!$result) return false;


        PlayerStatusDAO::update(new PlayerStatus($playerName, $gameId));
        return true;
    }
}