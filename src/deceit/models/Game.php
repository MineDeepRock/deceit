<?php

namespace deceit\models;


class Game
{
    private GameId $gameId;
    private string $gameOwnerName;

    private int $maxPlayers;
    private int $wolfsCount;

    private array $playersName;
    private array $wolfNameList;

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
        $this->wolfNameList = $wolfsName;
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

    public function start(): void {
        $this->isStarted = true;
        $this->timer->start();
    }

    public function canJoin(string $playerName): bool {
        if (in_array($playerName, $this->playersName)) return false;
        if (count($this->playersName) === $this->maxPlayers) return false;
        if ($this->isStarted) return false;

        return true;
    }

    public function addPlayer(string $playerName): bool {
        if ($this->canJoin($playerName)) {
            $this->playersName[] = $playerName;
            return true;
        }

        return false;
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

    /**
     * @return Map
     */
    public function getMap(): Map {
        return $this->map;
    }

    /**
     * @return int
     */
    public function getMaxPlayers(): int {
        return $this->maxPlayers;
    }

    /**
     * @return int
     */
    public function getWolfsCount(): int {
        return $this->wolfsCount;
    }

    /**
     * @return string[]
     */
    public function getPlayersName(): array {
        return $this->playersName;
    }

    /**
     * @param array $wolfNameList
     * @return bool
     */
    public function setWolfNameList(array $wolfNameList): bool {
        if ($this->isStarted) return false;
        if (count($this->wolfNameList) >= $this->wolfsCount) return false;

        $this->wolfNameList = $wolfNameList;
        return true;
    }

    /**
     * @return array
     */
    public function getWolfNameList(): array {
        return $this->wolfNameList;
    }
}