<?php

namespace deceit\services;


use deceit\dao\MapDAO;
use deceit\models\Game;
use deceit\models\GameTimer;
use deceit\models\Timer;
use deceit\storages\GameStorage;
use pocketmine\scheduler\TaskScheduler;

class CreateGameService
{
    static function execute(string $gameOwnerName, string $mapName, int $maxPlayers, int $wolfsCount, TaskScheduler $scheduler): bool {
        $game = Game::asNew($gameOwnerName, MapDAO::findByName($mapName), $maxPlayers, $wolfsCount, $scheduler);
        $result = GameStorage::add($game);

        if (!$result) return false;


        //オーナーも参加させる
        JoinGameService::execute($game->getGameId(), $game->getGameOwnerName());
        return true;
    }
}