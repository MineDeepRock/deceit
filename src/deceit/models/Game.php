<?php

namespace deceit\models;


class Game
{
    private GameId $gameId;
    private string $gameOwnerName;

    private int $maxPlayers;
    private int $wolfsCount;

    private array $playersName;
    private array $wolfsName;

    private array $fuelTanks;

    private Map $map;
    private Timer $timer;

    private bool $isStarted;

    private function __construct(GameId $gameId, string $gameOwnerName, int $maxPlayers, int $wolfsCount, array $playersName, array $wolfsName, array $fuelTanks, Map $map, Timer $timer, bool $isStarted) {
        $this->gameId = $gameId;
        $this->gameOwnerName = $gameOwnerName;
        $this->maxPlayers = $maxPlayers;
        $this->wolfsCount = $wolfsCount;
        $this->playersName = $playersName;
        $this->wolfsName = $wolfsName;
        $this->fuelTanks = $fuelTanks;
        $this->map = $map;
        $this->timer = $timer;
        $this->isStarted = $isStarted;
    }

    static function asNew(string $gameOwnerName, Map $map, Timer $timer, int $maxPlayers, int $wolfsCount): self {

        $fuelTanks = [];
        foreach ($map->getFuelSpawnVectors() as $fuelSpawnVector) {
            $fuelTanks[] = FuelTank::asNew();
        }

        return new Game(
            GameId::asNew(),
            $gameOwnerName,
            $maxPlayers,
            $wolfsCount,
            [],
            [],
            $fuelTanks,
            $map,
            $timer,
            false
        );
    }

    public function addPlayer(string $playerName): bool {
        if (in_array($playerName, $this->playersName)) return false;
        if (count($this->playersName) === $this->maxPlayers) return false;
        if ($this->isStarted) return false;

        $this->playersName[] = $playerName;
        return true;
    }

    public function removePlayer(string $playerName): bool {
        if (!in_array($playerName, $this->playersName)) return false;

        $index = array_search($playerName, $this->playersName);
        unset($this->playersName[$index]);
        $this->playersName = array_values($this->playersName);

        if ($playerName === $this->gameOwnerName) {
            $this->gameOwnerName = $this->playersName[0];
        }

        return true;
    }

    /**
     * @return GameId
     */
    public function getGameId(): GameId {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function getGameOwnerName(): string {
        return $this->gameOwnerName;
    }
}