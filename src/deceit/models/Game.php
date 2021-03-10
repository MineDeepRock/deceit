<?php

namespace deceit\models;


use deceit\data\WaitingRoom;
use deceit\pmmp\entities\FuelEntity;
use deceit\pmmp\entities\FuelTankEntity;
use deceit\pmmp\utilities\ExitTimer;
use deceit\pmmp\utilities\GameTimer;
use deceit\storages\GameStorage;
use deceit\storages\WaitingRoomStorage;
use deceit\types\FuelTankId;
use deceit\types\GameId;
use pocketmine\level\Position;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

//TODO:ユーザーがTankのキャパ設定をできるように
class Game
{
    const ExitRange = 3;

    private GameId $gameId;
    private string $gameOwnerName;

    private int $maxPlayers;
    private int $wolfsCount;

    private array $playerNameList;//TODO:rename
    private array $wolfNameList;

    /**
     * @var FuelTank[]
     */
    private array $fuelTanks;

    private Map $map;
    private GameTimer $timer;
    private ExitTimer $exitTimer;

    private TaskScheduler $scheduler;
    private TaskHandler $fuelSpawnHandler;

    private bool $isStarted;
    private bool $isFinished;

    private WaitingRoom $waitingRoom;

    public function __construct(string $gameOwnerName, Map $map, int $maxPlayers, int $wolfsCount, WaitingRoom $waitingRoom, TaskScheduler $scheduler) {
        $this->gameId = GameId::asNew();
        $fuelTanks = [];
        foreach ($map->getFuelTankMapDataList() as $fuelTankMapData) {
            $fuelTanks[] = new FuelTank($this->gameId, $fuelTankMapData->getCapacity());
        }

        $this->scheduler = $scheduler;
        $timer = new GameTimer($this->gameId, $scheduler);
        $exitTimer = new ExitTimer($this->gameId, $scheduler);

        $this->gameOwnerName = $gameOwnerName;
        $this->maxPlayers = $maxPlayers;
        $this->wolfsCount = $wolfsCount;
        $this->playerNameList = [];
        $this->wolfNameList = [];
        $this->fuelTanks = $fuelTanks;
        $this->map = $map;
        $this->timer = $timer;
        $this->exitTimer = $exitTimer;
        $this->isStarted = false;
        $this->isFinished = false;
        $this->waitingRoom = $waitingRoom;
    }

    public function start(): void {

        //TODO:ここにあるのは微妙
        $level = Server::getInstance()->getLevelByName($this->map->getLevelName());
        $this->fuelSpawnHandler = $this->scheduler->scheduleDelayedRepeatingTask(new ClosureTask(
            function (int $currentTick) use ($level): void {
                //TODO:難易度調整
                $vectors = $this->map->getFuelSpawnVectors();
                $spawnCount = intval(count($vectors) / 2);
                $vectorIndexList = array_rand($vectors, $spawnCount);
                if (is_numeric($vectorIndexList)) {
                    $fuelEntity = new FuelEntity($level, Position::fromObject($vectors[$vectorIndexList], $level));
                    $fuelEntity->spawnToAll();
                } else {
                    foreach ($vectorIndexList as $index) {
                        $fuelEntity = new FuelEntity($level, Position::fromObject($vectors[$index], $level));
                        $fuelEntity->spawnToAll();
                    }
                }
            }
        ), 20 * 3, 20 * 30);

        $index = 0;
        foreach ($this->map->getFuelTankMapDataList() as $tankData) {
            $fuelEntity = new FuelTankEntity($level, Position::fromObject($tankData->getVector(), $level), $this->gameId, $this->getFuelTanks()[$index]->getTankId());
            $fuelEntity->spawnToAll();
            $index++;
        }

        $this->isStarted = true;
        $this->timer->start();
    }

    public function finish(): void {
        $this->isFinished = true;
        $this->timer->stop();
        $this->exitTimer->stop();
        $this->fuelSpawnHandler->cancel();
    }

    public function startExitTimer(): void {
        $this->timer->stop();
        $this->exitTimer->start();
        $this->fuelSpawnHandler->cancel();
    }

    public function canJoin(string $playerName): bool {
        if (in_array($playerName, $this->playerNameList)) return false;
        if (count($this->playerNameList) === $this->maxPlayers) return false;
        if ($this->isStarted) return false;

        return true;
    }

    public function addPlayer(string $playerName): bool {
        if ($this->canJoin($playerName)) {
            $this->playerNameList[] = $playerName;
            return true;
        }

        return false;
    }

    public function removePlayer(string $playerName): bool {
        if (!in_array($playerName, $this->playerNameList)) return false;

        //オーナー以外に参加者がいなかったら、試合を削除
        if (count($this->playerNameList) === 1) {
            GameStorage::delete($this->gameId);

            return true;
        }

        //playersNameから削除
        $index = array_search($playerName, $this->playerNameList);
        unset($this->playerNameList[$index]);
        $this->playerNameList = array_values($this->playerNameList);

        //オーナーを受け渡す
        if ($playerName === $this->gameOwnerName) {
            $this->gameOwnerName = $this->playerNameList[0];
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
    public function getPlayerNameList(): array {
        return $this->playerNameList;
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

    public function getGameTimeLeft(): int {
        return $this->timer->getTimeLeft();
    }

    public function getGameTimeInitial(): int {
        return $this->timer->getInitialTime();
    }

    public function getGameTimerPercentage(): float {
        if ($this->timer->getTimeLeft() === 0) return 0.01;
        return $this->timer->getTimeLeft() / $this->timer->getInitialTime();
    }

    public function getExitTimeLeft(): int {
        return $this->exitTimer->getTimeLeft();
    }

    public function getExitTimeInitial(): int {
        return $this->exitTimer->getInitialTime();
    }

    public function getExitTimerPercentage(): float {
        if ($this->exitTimer->getTimeLeft() === 0) return 0.01;
        return $this->exitTimer->getTimeLeft() / $this->exitTimer->getInitialTime();
    }

    /**
     * @return array
     */
    public function getWolfNameList(): array {
        return $this->wolfNameList;
    }

    /**
     * @return WaitingRoom
     */
    public function getWaitingRoom(): WaitingRoom {
        return $this->waitingRoom;
    }
}