<?php


namespace deceit\adapters;


use deceit\models\GameId;
use deceit\models\PlayerStatus;

class PlayerStatusJsonAdapter
{
    static function decode(array $json): PlayerStatus {
        $gameId = $json["belong_game_id"] === null ? null : new GameId($json["belong_game_id"]);
        return new PlayerStatus($json["name"], $gameId);
    }

    static function encode(PlayerStatus $playerStatus): array {
        return [
            "name" => $playerStatus->getName(),
            "belong_game_id" => $playerStatus->getBelongGameId(),
        ];
    }
}