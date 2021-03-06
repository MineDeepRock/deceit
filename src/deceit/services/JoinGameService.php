<?php


namespace deceit\services;


use deceit\dao\PlayerDataDAO;
use deceit\pmmp\events\UpdatedGameDataEvent;
use deceit\types\GameId;
use deceit\models\PlayerData;
use deceit\storages\GameStorage;
use deceit\types\PlayerStateOnGame;

class JoinGameService
{
    static function execute(GameId $gameId, string $playerName): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;

        //PlayerData更新
        $result = $game->addPlayer($playerName);
        if (!$result) return false;

        UpdatePlayerStateOnGameService::execute($playerName, PlayerStateOnGame::Alive());

        PlayerDataDAO::update(new PlayerData($playerName, $gameId));

        $event = new UpdatedGameDataEvent($gameId);
        $event->call();
        return true;
    }
}