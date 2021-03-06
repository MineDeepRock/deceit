<?php


namespace deceit\services;


use deceit\models\PlayerStatus;
use deceit\storages\PlayerStatusStorage;
use deceit\types\PlayerStateOnGame;

class UpdatePlayerStateOnGameService
{
    static function execute(string $playerName, PlayerStateOnGame $playerStateOnGame): void {
        $playerStatus = PlayerStatusStorage::findByName($playerName);
        if ($playerStatus === null) return;

        PlayerStatusStorage::update(new PlayerStatus($playerName, $playerStatus->getBelongGameId(), $playerStateOnGame));
    }
}