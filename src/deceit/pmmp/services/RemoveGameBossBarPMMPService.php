<?php


namespace deceit\pmmp\services;


use bossbar_system\BossBar;
use bossbar_system\model\BossBarType;
use deceit\storages\GameStorage;
use deceit\types\GameId;
use pocketmine\Server;

class RemoveGameBossBarPMMPService
{
    static function execute(GameId $gameId, BossBarType $bossBarType): void {
        $game = GameStorage::findById($gameId);
        if ($game === null) return;

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;
            $bossBar = BossBar::findByType($player, $bossBarType);
            $bossBar->remove();
        }
    }
}