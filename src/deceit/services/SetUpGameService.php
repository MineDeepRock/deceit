<?php

namespace deceit\services;


use deceit\dao\MapDAO;
use deceit\models\Game;
use deceit\models\Timer;
use deceit\storages\GameStorage;
use pocketmine\scheduler\TaskScheduler;

class SetUpGameService
{
    static function execute(string $gameOwnerName, string $mapName, $maxTime, int $maxPlayers, int $wolfsCount, TaskScheduler $scheduler): void {
        $game = Game::asNew($gameOwnerName, MapDAO::findByName($mapName), Timer::asNew($maxTime, $scheduler), $maxPlayers, $wolfsCount);
        $result = GameStorage::add($game);
        if ($result) JoinGameService::execute($game->getGameId(), $game->getGameOwnerName());
    }
}