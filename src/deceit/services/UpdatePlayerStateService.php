<?php


namespace deceit\services;


use deceit\models\PlayerStatus;
use deceit\storages\PlayerStatusStorage;
use deceit\types\PlayerState;

class UpdatePlayerStateService
{
    static function execute(string $playerName, PlayerState $playerState): void {
        $playerStatus = PlayerStatusStorage::findByName($playerName);
        if ($playerStatus === null) return;

        PlayerStatusStorage::update(new PlayerStatus($playerName, $playerStatus->getBelongGameId(), $playerState));
    }
}