<?php


namespace deceit\pmmp\services;


use deceit\types\GameId;
use deceit\pmmp\scoreboards\GameSettingsScoreboard;
use deceit\pmmp\scoreboards\LobbyScoreboard;
use deceit\services\JoinGameService;
use deceit\storages\GameStorage;
use pocketmine\Player;
use pocketmine\Server;

class JoinGamePMMPService
{
    static function execute(Player $player, GameId $gameId): void {
        $result = JoinGameService::execute($gameId, $player->getName());
        if ($result) {
            $player->sendMessage("ゲームに参加しました");
            LobbyScoreboard::delete($player);
            GameSettingsScoreboard::send($player);

            $game = GameStorage::findById($gameId);

            //TODO:待合室がロビー以外を考慮する
            $player->teleport($game->getWaitingRoom()->getVector());

            foreach ($game->getPlayerNameList() as $participantName) {
                $participant = Server::getInstance()->getPlayer($participantName);
                if ($participant->getName() === $player->getName()) continue;

                $participant->sendMessage($player->getName() . "がゲームに参加しました");
            }
        } else {
            $player->sendMessage("ゲームに参加できませんでした");
        }
    }
}