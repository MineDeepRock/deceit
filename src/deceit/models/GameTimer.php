<?php


namespace deceit\models;


use deceit\pmmp\events\FinishedGameTimerEvent;
use deceit\pmmp\events\StoppedGameTimerEvent;
use deceit\pmmp\events\UpdatedGameTimerEvent;
use pocketmine\scheduler\TaskScheduler;

class GameTimer extends Timer
{
    private GameId $gameId;

    public function __construct(GameId $gameId, TaskScheduler $scheduler) {
        $this->gameId = $gameId;
        parent::__construct(600, 0, $scheduler);
    }

    public function onUpdatedTimer(): void {
        $event = new UpdatedGameTimerEvent($this->gameId);
        $event->call();
    }

    public function onStoppedTimer(): void {
        $event = new StoppedGameTimerEvent($this->gameId);
        $event->call();
    }

    public function onFinishedTimer(): void {
        $event = new FinishedGameTimerEvent($this->gameId);
        $event->call();
    }
}