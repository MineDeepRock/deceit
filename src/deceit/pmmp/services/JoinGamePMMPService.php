<?php


namespace deceit\pmmp\services;


use deceit\models\GameId;
use deceit\services\JoinGameService;
use pocketmine\Player;

class JoinGamePMMPService
{
    static function execute(Player $player, GameId $gameId): void {
        $result = JoinGameService::execute($gameId, $player->getName());
        if ($result) {
            $player->sendMessage("ゲームに参加しました");
        } else {
            $player->sendMessage("ゲームに参加できませんでした");
        }
    }
}