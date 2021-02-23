<?php


namespace deceit\pmmp\services;


use bossbar_system\BossBar;
use deceit\models\GameId;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\storages\GameStorage;
use pocketmine\Server;

class FinishGamePMMPService
{
    //勝敗 マップ直し
    static function execute(GameId $gameId): void {
        $game = GameStorage::findById($gameId);
        if ($game === null) return;


        if (count($game->getEscapedPlayerNameList()) === 0) {

            foreach ($game->getWolfNameList() as $escapedPlayerName) {
                $player = Server::getInstance()->getPlayer($escapedPlayerName);
                if ($player === null) return;

                $player->sendMessage("勝利！！");
                $player->sendTitle("勝利！！");
            }
        } else {

            foreach ($game->getEscapedPlayerNameList() as $escapedPlayerName) {
                $escapedPlayer = Server::getInstance()->getPlayer($escapedPlayerName);
                if ($escapedPlayer === null) return;

                $escapedPlayer->sendMessage("勝利！！");
                $escapedPlayer->sendTitle("勝利！！");
            }

            foreach ($game->getDeadPlayerNameList() as $deadPlayerName) {
                $deadPlayer = Server::getInstance()->getPlayer($deadPlayerName);
                if ($deadPlayer === null) return;

                $deadPlayer->sendMessage("勝利！！");
                $deadPlayer->sendTitle("勝利！！");
            }
        }

        $level = Server::getInstance()->getLevelByName("lobby");
        foreach ($game->getPlayersName() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            $player->teleport($level->getSpawnLocation());
            $bossBars = BossBar::getBelongings($player);
            foreach ($bossBars as $bossBar) $bossBar->remove();
            LobbyScoreboard::send($player);
        }
    }
}