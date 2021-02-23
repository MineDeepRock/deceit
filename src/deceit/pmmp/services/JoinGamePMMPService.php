<?php


namespace deceit\pmmp\services;


use deceit\models\GameId;
use deceit\pmmp\scoreboards\GameSettingsScoreboard;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\services\JoinGameService;
use pocketmine\Player;

class JoinGamePMMPService
{
    static function execute(Player $player, GameId $gameId): void {
        $result = JoinGameService::execute($gameId, $player->getName());
        if ($result) {
            $player->sendMessage("ゲームに参加しました");
            LobbyScoreboard::delete($player);
            GameSettingsScoreboard::send($player);
        } else {
            $player->sendMessage("ゲームに参加できませんでした");
        }
    }
}