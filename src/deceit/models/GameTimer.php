<?php


namespace deceit\models;


use deceit\pmmp\BossBarTypeList;
use deceit\pmmp\services\FinishGamePMMPService;
use deceit\pmmp\services\RemoveGameBossBarPMMPService;
use deceit\pmmp\services\UpdateGameBossBarPMMPService;
use deceit\services\FinishGameService;
use deceit\types\GameId;
use pocketmine\scheduler\TaskScheduler;

class GameTimer extends Timer
{
    private GameId $gameId;

    public function __construct(GameId $gameId, TaskScheduler $scheduler) {
        $this->gameId = $gameId;
        parent::__construct(600, 0, $scheduler);
    }

    public function onUpdatedTimer(): void {
        UpdateGameBossBarPMMPService::execute($this->gameId, BossBarTypeList::GameTimer());
    }

    public function onStoppedTimer(): void {
        RemoveGameBossBarPMMPService::execute($this->gameId, BossBarTypeList::GameTimer());
    }

    public function onFinishedTimer(): void {
        FinishGamePMMPService::execute($this->gameId);
        FinishGameService::execute($$this->gameId);
    }
}