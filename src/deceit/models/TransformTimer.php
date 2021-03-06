<?php


namespace deceit\models;


use deceit\pmmp\services\TransformToPlayerPMMPService;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

class TransformTimer extends Timer
{
    private string $playerName;

    public function __construct(string $playerName, TaskScheduler $scheduler) {
        $this->playerName = $playerName;
        parent::__construct(30, 0, $scheduler);
    }

    public function onUpdatedTimer(): void {
        //TODO:ボスバー
    }

    public function onStoppedTimer(): void {
        $this->onFinishedTimer();
    }

    public function onFinishedTimer(): void {
        $player = Server::getInstance()->getPlayer($this->playerName);
        if ($player === null) return;
        TransformToPlayerPMMPService::execute($player);
    }
}