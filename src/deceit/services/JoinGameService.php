<?php


namespace deceit\services;


use deceit\dao\PlayerStatusDAO;
use deceit\pmmp\events\UpdatedGameDataEvent;
use deceit\types\GameId;
use deceit\models\PlayerStatus;
use deceit\storages\GameStorage;
use deceit\types\PlayerStateOnGame;

class JoinGameService
{
    static function execute(GameId $gameId, string $playerName): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;

        //Status更新
        $result = $game->addPlayer($playerName);
        if (!$result) return false;

        UpdatePlayerStateOnGameService::execute($playerName, PlayerStateOnGame::Alive());

        PlayerStatusDAO::update(new PlayerStatus($playerName, $gameId));

        $event = new UpdatedGameDataEvent($gameId);
        $event->call();
        return true;
    }
}