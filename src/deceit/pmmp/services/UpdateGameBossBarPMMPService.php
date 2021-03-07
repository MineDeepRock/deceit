<?php


namespace deceit\pmmp\services;


use bossbar_system\BossBar;
use bossbar_system\model\BossBarType;
use deceit\pmmp\BossBarTypeList;
use deceit\storages\GameStorage;
use deceit\types\GameId;
use pocketmine\Server;

class UpdateGameBossBarPMMPService
{
    static function execute(GameId $gameId, BossBarType $bossBarType): void {
        $game = GameStorage::findById($gameId);
        if ($game === null) return;

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            //BossBarnの更新
            $bossBar = BossBar::findByType($player, $bossBarType);
            if ($bossBar === null) return;//TODO:error

            if ($bossBarType->equals(BossBarTypeList::GameTimer())) {
                $bossBar->updateTitle("残り時間:" . ($game->getGameTimeLeft() - $game->getGameTimeLeft()));
                $bossBar->updatePercentage($game->getGameTimerPercentage());

            } else if ($bossBarType->equals(BossBarTypeList::ExitTimer())) {
                $bossBar->updateTitle("出口が閉まるまで:" . ($game->getExitTimeInitial() - $game->getExitTimeLeft()));
                $bossBar->updatePercentage($game->getExitTimerPercentage());

            }
        }
    }
}