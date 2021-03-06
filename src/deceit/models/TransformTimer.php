<?php


namespace deceit\models;


use bossbar_system\BossBar;
use deceit\pmmp\BossBarTypeList;
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

    public function start(): void {
        $player = Server::getInstance()->getPlayer($this->playerName);
        if ($player === null) return;

        $bossBar = new BossBar($player, BossBarTypeList::Transform(), "Transform", 0.0);
        $bossBar->send();

        parent::start();
    }

    public function onUpdatedTimer(): void {
        $player = Server::getInstance()->getPlayer($this->playerName);
        if ($player === null) return;

        $bossBar = BossBar::findByType($player, BossBarTypeList::Transform());
        if ($bossBar === null) return;//TODO:error
        $bossBar->updatePercentage($this->getTimeLeft() / $this->getInitialTime());
    }

    public function onStoppedTimer(): void {
        $this->onFinishedTimer();
    }

    public function onFinishedTimer(): void {
        $player = Server::getInstance()->getPlayer($this->playerName);
        if ($player === null) return;

        TransformToPlayerPMMPService::execute($player);

        $bossBar = BossBar::findByType($player, BossBarTypeList::Transform());
        if ($bossBar === null) return;//TODO:error
        $bossBar->remove();
    }
}