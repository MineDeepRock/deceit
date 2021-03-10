<?php


namespace deceit\pmmp\utilities;


use bossbar_system\BossBar;
use deceit\models\Game;
use deceit\pmmp\BossBarTypeList;
use deceit\pmmp\services\FinishGamePMMPService;
use deceit\services\FinishGameService;
use deceit\storages\GameStorage;
use deceit\types\GameId;
use pocketmine\level\particle\DustParticle;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

class ExitTimer extends Timer
{
    private GameId $gameId;

    public function __construct(GameId $gameId, TaskScheduler $scheduler) {
        $this->gameId = $gameId;
        parent::__construct(60, 0, $scheduler);
    }

    public function start(): void {
        $game = GameStorage::findById($this->gameId);
        if ($game === null) return;

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            $bossBar = new BossBar($player, BossBarTypeList::ExitTimer(), "", 1);
            $bossBar->send();
        }

        parent::start();
    }

    public function onUpdatedTimer(): void {
        //BossBarnの更新
        $game = GameStorage::findById($this->gameId);
        if ($game === null) return;

        $level = Server::getInstance()->getLevelByName($game->getMap()->getLevelName());
        for ($degree = 0; $degree < 360; $degree += 10) {
            $center = $game->getMap()->getExitVector();

            $x = Game::ExitRange * sin(deg2rad($degree));
            $z = Game::ExitRange * cos(deg2rad($degree));

            $pos = $center->add($x, 3, $z);
            $level->addParticle(new DustParticle($pos, 0, 255, 0));
        }

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            $bossBar = BossBar::findByType($player, BossBarTypeList::ExitTimer());
            if ($bossBar === null) return;//TODO:error

            $bossBar->updateTitle("残り時間:" . ($game->getExitTimeInitial() - $game->getExitTimeLeft()));
            $bossBar->updatePercentage(1 - $game->getExitTimerPercentage());
        }
    }

    public function onStoppedTimer(): void {
        $this->removeBossBar();
    }

    public function onFinishedTimer(): void {
        $this->removeBossBar();

        FinishGamePMMPService::execute($this->gameId);
        FinishGameService::execute($this->gameId);
    }

    private function removeBossBar() {
        $game = GameStorage::findById($this->gameId);
        if ($game === null) return;

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;
            $bossBar = BossBar::findByType($player, BossBarTypeList::ExitTimer());
            if ($bossBar !== null) $bossBar->remove();
        }
    }
}