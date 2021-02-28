<?php


namespace deceit\pmmp\services;


use deceit\storages\PlayerDataOnGameStorage;
use deceit\types\GameId;
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

        //メッセージの送信+ロビーへ送還
        $escapedPlayerCount = PlayerDataOnGameStorage::getEscapedPlayers($gameId);
        $winWolfs = $escapedPlayerCount === 0;

        $messageToPlayers = $winWolfs ? "敗北" : "勝利!!";
        $messageToWolfs = $winWolfs ? "勝利!!" : "敗北";

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player === null) return;

            if (in_array($playerName, $game->getWolfNameList())) {
                $player->sendMessage($messageToWolfs);
                $player->sendTitle($messageToWolfs);
            } else {
                $player->sendMessage($messageToPlayers);
                $player->sendTitle($messageToPlayers);
            }

            QuitGamePMMPService::execute($player);
        }
    }
}