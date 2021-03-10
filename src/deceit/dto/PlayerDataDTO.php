<?php


namespace deceit\dto;


use deceit\types\GameId;
use deceit\data\PlayerData;

class PlayerDataDTO
{
    static function decode(array $json): PlayerData {
        $gameId = $json["belong_game_id"] === null ? null : new GameId($json["belong_game_id"]);
        return new PlayerData($json["name"], $gameId);
    }

    static function encode(PlayerData $playerData): array {
        $gameId = $playerData->getBelongGameId() === null ? null : strval($playerData->getBelongGameId());

        return [
            "name" => $playerData->getName(),
            "belong_game_id" => $gameId,
        ];
    }
}