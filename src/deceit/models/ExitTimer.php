<?php


namespace deceit\models;


use deceit\pmmp\events\UpdatedExitTimerEvent;
use deceit\pmmp\services\FinishGamePMMPService;
use deceit\services\FinishGameService;
use deceit\types\GameId;
use pocketmine\scheduler\TaskScheduler;

class ExitTimer extends Timer
{
    private GameId $gameId;

    public function __construct(GameId $gameId, TaskScheduler $scheduler) {
        $this->gameId = $gameId;
        parent::__construct(60, 0, $scheduler);
    }

    public function onUpdatedTimer(): void {
        $event = new UpdatedExitTimerEvent($this->gameId);
        $event->call();
    }

    public function onStoppedTimer(): void {}

    public function onFinishedTimer(): void {
        FinishGamePMMPService::execute($this->gameId);
        FinishGameService::execute($this->gameId);
    }
}