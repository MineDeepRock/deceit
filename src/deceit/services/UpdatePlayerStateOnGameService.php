<?php


namespace deceit\services;


use deceit\models\PlayerDataOnGame;
use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\PlayerStateOnGame;

class UpdatePlayerStateOnGameService
{
    static function execute(string $playerName, PlayerStateOnGame $playerStateOnGame): void {
        $playerDataOnGame = PlayerDataOnGameStorage::findByName($playerName);
        if ($playerDataOnGame === null) return;

        PlayerDataOnGameStorage::update(new PlayerDataOnGame($playerName, $playerDataOnGame->getBelongGameId(), $playerStateOnGame));
    }
}