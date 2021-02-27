<?php


namespace deceit\storages;


use deceit\models\PlayerDataOnGame;
use deceit\types\GameId;
use deceit\types\PlayerStateOnGame;

class PlayerDataOnGameStorage
{
    /**
     * @var PlayerDataOnGame[]
     */
    static array $playerDataOnGames = [];

    static function add(PlayerDataOnGame $playerDataOnGame): bool {
        if (self::findByName($playerDataOnGame->getName()) !== null) return false;

        self::$playerDataOnGames[] = $playerDataOnGame;
        return true;
    }

    static function delete(string $name): void {

        foreach (self::$playerDataOnGames as $key => $playerDataOnGame) {
            if ($playerDataOnGame->getName() === $name) unset(self::$playerDataOnGames[$key]);
        }

        self::$playerDataOnGames = array_values(self::$playerDataOnGames);
    }

    static function deleteAll(): void {
        self::$playerDataOnGames = [];
    }

    static function findByName(string $name): ?PlayerDataOnGame {
        if ($name === null) return null;

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getName() === $name) {
                return $playerDataOnGame;
            }
        }

        return null;
    }

    static function update(PlayerDataOnGame $playerDataOnGame) {
        self::delete($playerDataOnGame->getName());
        self::add($playerDataOnGame);
    }

    /**
     * @return PlayerDataOnGame[]
     */
    static function getAll(): array {
        return self::$playerDataOnGames;
    }

    /**
     * @param GameId $gameId
     * @return PlayerDataOnGame[]
     */
    static function getPlayers(GameId $gameId): array {
        $result = [];

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getBelongGameId()->equals($gameId)) {
                $result[] = $playerDataOnGame;
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @return PlayerDataOnGame[]
     */
    static function getAlivePlayers(GameId $gameId): array {
        $result = [];

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getBelongGameId()->equals($gameId)) {
                if ($playerDataOnGame->getState()->equals(PlayerStateOnGame::Alive())) {
                    $result[] = $playerDataOnGame;
                }
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @return PlayerDataOnGame[]
     */
    static function getCadaverPlayers(GameId $gameId): array {
        $result = [];

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getBelongGameId()->equals($gameId)) {
                if ($playerDataOnGame->getState()->equals(PlayerStateOnGame::Cadaver())) {
                    $result[] = $playerDataOnGame;
                }
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @return PlayerDataOnGame[]
     */
    static function getDeadPlayers(GameId $gameId): array {
        $result = [];

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getBelongGameId()->equals($gameId)) {
                if ($playerDataOnGame->getState()->equals(PlayerStateOnGame::Dead())) {
                    $result[] = $playerDataOnGame;
                }
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @return PlayerDataOnGame[]
     */
    static function getEscapedPlayers(GameId $gameId): array {
        $result = [];

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getBelongGameId()->equals($gameId)) {
                if ($playerDataOnGame->getState()->equals(PlayerStateOnGame::Escaped())) {
                    $result[] = $playerDataOnGame;
                }
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @return PlayerDataOnGame[]
     */
    static function getWolfs(GameId $gameId): array {
        $result = [];

        foreach (self::$playerDataOnGames as $playerDataOnGame) {
            if ($playerDataOnGame->getBelongGameId()->equals($gameId)) {
                if ($playerDataOnGame->isWolf()) {
                    $result[] = $playerDataOnGame;
                }
            }
        }

        return $result;
    }
}