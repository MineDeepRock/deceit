<?php


namespace deceit\services;


use deceit\models\PlayerStatus;
use deceit\storages\PlayerStatusStorage;
use deceit\types\GameId;
use deceit\storages\GameStorage;
use deceit\types\PlayerState;
use pocketmine\scheduler\TaskScheduler;

class SelectWolfPlayersService
{
    static function execute(GameId $gameId, TaskScheduler $taskScheduler): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;

        $wolfPlayersName = [];
        $wolfPlayersNameIndex = array_rand($game->getPlayerNameList(), $game->getWolfsCount());

        if (is_numeric($wolfPlayersNameIndex)) {
            $wolfPlayerName = $game->getPlayerNameList()[$wolfPlayersNameIndex];
            $wolfPlayersName[] = $wolfPlayerName;

            PlayerStatusStorage::update(new PlayerStatus($wolfPlayerName, $gameId, PlayerState::Alive(), true, $taskScheduler));
        } else {
            foreach ($wolfPlayersNameIndex as $index) {
                $wolfPlayerName = $game->getPlayerNameList()[$index];
                $wolfPlayersName[] = $wolfPlayerName;

                PlayerStatusStorage::update(new PlayerStatus($wolfPlayerName, $gameId, PlayerState::Alive(), true, $taskScheduler));
            }
        }

        $game->setWolfNameList($wolfPlayersName);

        return true;
    }
}