<?php

namespace deceit\storages;


use deceit\models\Game;
use deceit\models\GameId;

class GameStorage
{
    static array $games = [];

    static function add(Game $game): void {
        self::$games[] = $game;
    }

    static function delete(GameId $gameId): void {

        foreach (self::$games as $key => $game) {
            if ($game->getId()->equals($gameId)) unset(self::$games[$key]);
        }

        self::$games = array_values(self::$games);
    }

    static function deleteAll():void {
        self::$games = [];
    }

    static function findById(GameId $gameId): ?Game {

        foreach (self::$games as $game) {
            if ($game->getId()->equals($gameId)) {
                return $game;
            }
        }

        return null;
    }

    /**
     * @return array|Game[]
     */
    static function getAll(): array {
        return self::$games;
    }
}