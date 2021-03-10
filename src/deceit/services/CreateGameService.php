<?php

namespace deceit\services;


use deceit\dao\MapDAO;
use deceit\dao\PlayerDataDAO;
use deceit\models\Game;
use deceit\storages\GameStorage;
use deceit\storages\WaitingRoomStorage;
use pocketmine\scheduler\TaskScheduler;

class CreateGameService
{
    static function execute(string $gameOwnerName, string $mapName, int $maxPlayers, int $wolfsCount, TaskScheduler $scheduler): bool {
        $ownerData = PlayerDataDAO::findByName($gameOwnerName);
        if ($ownerData->getBelongGameId() !== null) return false;

        $waitingRoom = WaitingRoomStorage::useRandomAvailableRoom();
        if ($waitingRoom === null) return false;

        $game = new Game($gameOwnerName, MapDAO::findByName($mapName), $maxPlayers, $wolfsCount, $waitingRoom, $scheduler);
        $result = GameStorage::add($game);

        if (!$result) return false;


        //オーナーも参加させる
        JoinGameService::execute($game->getGameId(), $game->getGameOwnerName());
        return true;
    }
}