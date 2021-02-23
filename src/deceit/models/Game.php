<?php

namespace deceit\models;


use pocketmine\scheduler\TaskScheduler;

class Game
{
    private GameId $gameId;
    private string $gameOwnerName;

    private int $maxPlayers;
    private int $wolfsCount;

    private array $playersName;//TODO:rename
    private array $wolfNameList;

    private array $alivePlayerNameList;
    private array $deadPlayerNameList;

    private array $escapedPlayerNameList;

    /**
     * @var FuelTank[]
     */
    private array $fuelTanks;

    private Map $map;
    private GameTimer $timer;
    private ExitTimer $exitTimer;

    private bool $isStarted;
    private bool $isFinished;

    public function __construct(string $gameOwnerName, Map $map, int $maxPlayers, int $wolfsCount, TaskScheduler $scheduler) {
        $gameId = GameId::asNew();
        $fuelTanks = [];
        foreach ($map->getFuelSpawnVectors() as $fuelSpawnVector) {
            $fuelTanks[] = FuelTank::asNew($gameId);
        }

        $timer = new GameTimer($gameId, $scheduler);
        $exitTimer = new ExitTimer($gameId, $scheduler);

        $this->gameId = $gameId;
        $this->gameOwnerName = $gameOwnerName;
        $this->maxPlayers = $maxPlayers;
        $this->wolfsCount = $wolfsCount;
        $this->playersName = [];
        $this->wolfNameList = [];
        $this->fuelTanks = $fuelTanks;
        $this->map = $map;
        $this->timer = $timer;
        $this->exitTimer = $exitTimer;
        $this->isStarted = false;
        $this->isFinished = false;
        $this->alivePlayerNameList = [];
        $this->deadPlayerNameList = [];
        $this->escapedPlayerNameList = [];
    }

    public function start(): void {
        $this->isStarted = true;
        $this->timer->start();
    }

    public function finish(): void {
        $this->isFinished = true;
        $this->exitTimer->stop();

    }

    public function startExitTimer(): void {
        $this->timer->stop();
        $this->exitTimer->start();
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
            $this->alivePlayerNameList[] = $playerName;
            return true;
        }

        return false;
    }

    public function removePlayer(string $playerName): bool {
        if (!in_array($playerName, $this->playersName)) return false;

        $index = array_search($playerName, $this->playersName);
        unset($this->playersName[$index]);
        $this->playersName = array_values($this->playersName);

        //オーナーを受け渡す
        if ($playerName === $this->gameOwnerName) {
            $this->gameOwnerName = $this->playersName[0];
        }

        //他のListからも削除
        $this->removeAlivePlayerName($playerName);
        $this->removeDeadPlayerName($playerName);
        $this->removeEscapedPlayerName($playerName);

        return true;
    }

    public function addAlivePlayerName(string $name): void {
        //参加していない
        if (!in_array($name, $this->playersName)) return;
        //追加済み
        if (in_array($name, $this->alivePlayerNameList)) return;

        //DeadPlayerから削除
        $this->removeDeadPlayerName($name);

        $this->alivePlayerNameList[] = $name;
    }

    private function removeAlivePlayerName(string $name): void {
        //参加していない
        if (!in_array($name, $this->playersName)) return;
        //いない
        if (!in_array($name, $this->alivePlayerNameList)) return;

        $index = array_search($name, $this->alivePlayerNameList);
        unset($this->alivePlayerNameList[$index]);
        $this->alivePlayerNameList = array_values($this->alivePlayerNameList);
    }

    public function addDeadPlayerName(string $name): void {
        //参加していない
        if (!in_array($name, $this->playersName)) return;
        //追加済み
        if (in_array($name, $this->deadPlayerNameList)) return;
        //AlivePlayerから削除
        $this->removeAlivePlayerName($name);

        $this->deadPlayerNameList[] = $name;
    }

    public function removeDeadPlayerName(string $name): void {
        //参加していない
        if (!in_array($name, $this->playersName)) return;
        //いない
        if (!in_array($name, $this->deadPlayerNameList)) return;

        $index = array_search($name, $this->deadPlayerNameList);
        unset($this->deadPlayerNameList[$index]);
        $this->deadPlayerNameList = array_values($this->deadPlayerNameList);
    }

    public function addEscapedPlayerName(string $name): void {
        //参加していない
        if (!in_array($name, $this->playersName)) return;
        //追加済み
        if (in_array($name, $this->escapedPlayerNameList)) return;

        //AliveとDeadから削除
        $this->removeAlivePlayerName($name);
        $this->removeDeadPlayerName($name);

        $this->escapedPlayerNameList[] = $name;
    }

    public function removeEscapedPlayerName(string $name): void {
        //参加していない
        if (!in_array($name, $this->playersName)) return;
        //いない
        if (!in_array($name, $this->escapedPlayerNameList)) return;

        $index = array_search($name, $this->escapedPlayerNameList);
        unset($this->escapedPlayerNameList[$index]);
        $this->escapedPlayerNameList = array_values($this->escapedPlayerNameList);
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

    /**
     * @return FuelTank[]
     */
    public function getFuelTanks(): array {
        return $this->fuelTanks;
    }

    public function getFuelTankById(FuelTankId $id): ?FuelTank {
        foreach ($this->fuelTanks as $fuelTank) {
            if ($fuelTank->getTankId()->equals($id)) return $fuelTank;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool {
        return $this->isFinished;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool {
        return $this->isStarted;
    }

    /**
     * @return string[]
     */
    public function getAlivePlayerNameList(): array {
        return $this->alivePlayerNameList;
    }

    /**
     * @return string[]
     */
    public function getDeadPlayerNameList(): array {
        return $this->deadPlayerNameList;
    }

    public function getGameTimerPercentage(): float {
        if ($this->timer->getTimeLeft() === 0) return 0;
        return $this->timer->getTimeLeft() / $this->timer->getInitialTime();
    }

    public function getExitTimerPercentage(): float {
        if ($this->exitTimer->getTimeLeft() === 0) return 0;
        return $this->exitTimer->getTimeLeft() / $this->exitTimer->getInitialTime();
    }
}