<?php


namespace deceit\storages;


use deceit\models\PlayerStatus;
use deceit\types\GameId;
use deceit\types\PlayerStateOnGame;

class PlayerStatusStorage
{
    /**
     * @var PlayerStatus[]
     */
    static array $playerStatusList = [];

    static function add(PlayerStatus $playerStatus): bool {
        if (self::findByName($playerStatus->getName()) !== null) return false;

        self::$playerStatusList[] = $playerStatus;
        return true;
    }

    static function delete(string $name): void {

        foreach (self::$playerStatusList as $key => $playerStatus) {
            if ($playerStatus->getName() === $name) unset(self::$playerStatusList[$key]);
        }

        self::$playerStatusList = array_values(self::$playerStatusList);
    }

    static function deleteAll(): void {
        self::$playerStatusList = [];
    }

    static function findByName(string $name): ?PlayerStatus {
        if ($name === null) return null;

        foreach (self::$playerStatusList as $playerStatus) {
            if ($playerStatus->getName() === $name) {
                return $playerStatus;
            }
        }

        return null;
    }

    static function update(PlayerStatus $playerStatus) {
        self::delete($playerStatus->getName());
        self::add($playerStatus);
    }

    /**
     * @return PlayerStatus[]
     */
    static function getAll(): array {
        return self::$playerStatusList;
    }

    /**
     * @param GameId $gameId
     * @return PlayerStatus[]
     */
    static function getPlayers(GameId $gameId): array {
        $result = [];

        foreach (self::$playerStatusList as $playerStatus) {
            if ($playerStatus->getBelongGameId()->equals($gameId)) {
                $result[] = $playerStatus;
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @param PlayerStateOnGame $targetStateOnGame
     * @return PlayerStatus[]
     */
    private static function getPlayersByState(GameId $gameId, PlayerStateOnGame $targetStateOnGame): array {
        $result = [];

        foreach (self::$playerStatusList as $playerStatus) {
            if ($playerStatus->getBelongGameId()->equals($gameId)) {
                if ($playerStatus->getState()->equals($targetStateOnGame)) {
                    $result[] = $playerStatus;
                }
            }
        }

        return $result;
    }

    /**
     * @param GameId $gameId
     * @return PlayerStatus[]
     */
    static function getAlivePlayers(GameId $gameId): array {
        return self::getPlayersByState($gameId, PlayerStateOnGame::Alive());
    }

    /**
     * @param GameId $gameId
     * @return PlayerStatus[]
     */
    static function getCadaverPlayers(GameId $gameId): array {
        return self::getPlayersByState($gameId, PlayerStateOnGame::Cadaver());

    }

    /**
     * @param GameId $gameId
     * @return PlayerStatus[]
     */
    static function getDeadPlayers(GameId $gameId): array {
        return self::getPlayersByState($gameId, PlayerStateOnGame::Dead());
    }

    /**
     * @param GameId $gameId
     * @return PlayerStatus[]
     */
    static function getEscapedPlayers(GameId $gameId): array {
        return self::getPlayersByState($gameId, PlayerStateOnGame::Escaped());
    }
}