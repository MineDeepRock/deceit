<?php


namespace deceit\services;


use deceit\models\PlayerStatus;
use deceit\storages\PlayerStatusStorage;
use deceit\types\GameId;
use deceit\storages\GameStorage;
use deceit\types\PlayerStateOnGame;

class SelectWolfPlayersService
{
    static function execute(GameId $gameId): bool {
        $game = GameStorage::findById($gameId);
        if ($game === null) return false;

        $wolfPlayersName = [];
        $wolfPlayersNameIndex = array_rand($game->getPlayerNameList(), $game->getWolfsCount());

        if (is_numeric($wolfPlayersNameIndex)) {
            $wolfPlayerName = $game->getPlayerNameList()[$wolfPlayersNameIndex];
            $wolfPlayersName[] = $wolfPlayerName;

            PlayerStatusStorage::update(new PlayerStatus($wolfPlayerName, $gameId, PlayerStateOnGame::Alive(), true));
        } else {
            foreach ($wolfPlayersNameIndex as $index) {
                $wolfPlayerName = $game->getPlayerNameList()[$index];
                $wolfPlayersName[] = $wolfPlayerName;

                PlayerStatusStorage::update(new PlayerStatus($wolfPlayerName, $gameId, PlayerStateOnGame::Alive(), true));
            }
        }

        $game->setWolfNameList($wolfPlayersName);

        return true;
    }
}