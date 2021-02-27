<?php


namespace deceit\pmmp\services;


use bossbar_system\BossBar;
use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\GameId;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\storages\GameStorage;
use pocketmine\block\Block;
use pocketmine\Server;

class FinishGamePMMPService
{
    //勝敗 マップ直し
    static function execute(GameId $gameId): void {
        $game = GameStorage::findById($gameId);
        if ($game === null) return;

        $map = $game->getMap();
        $mapLevel = Server::getInstance()->getLevelByName($map->getLevelName());
        $mapLevel->setBlock($map->getExitVector(), Block::get($map->getOriginalExitBlockId()));

        $escapedPlayerCount = PlayerDataOnGameStorage::getEscapedPlayers($gameId);
        $winWolfs = $escapedPlayerCount === 0;

        $messageToPlayers = $winWolfs ? "敗北" : "勝利!!";
        $messageToWolfs = $winWolfs ? "勝利!!" : "敗北";

        foreach ($game->getPlayersName() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            $playerDataOnGame = PlayerDataOnGameStorage::findByName($playerName);
            if ($playerDataOnGame->isWolf()) {
                $player->sendMessage($messageToWolfs);
                $player->sendTitle($messageToWolfs);
            } else {
                $player->sendMessage($messageToPlayers);
                $player->sendTitle($messageToPlayers);
            }
        }

        $level = Server::getInstance()->getLevelByName("lobby");
        foreach ($game->getPlayersName() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            $player->teleport($level->getSpawnLocation());
            $player->getInventory()->setContents([]);
            $bossBars = BossBar::getBelongings($player);
            foreach ($bossBars as $bossBar) $bossBar->remove();
            LobbyScoreboard::send($player);
        }
    }
}