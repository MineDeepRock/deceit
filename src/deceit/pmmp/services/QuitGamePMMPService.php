<?php


namespace deceit\pmmp\services;


use deceit\services\QuitGameService;
use pocketmine\Player;

class QuitGamePMMPService
{
    static function execute(Player $player): void {
        if (!$player->isOnline()) return;

        $result = QuitGameService::execute($player->getName());
        if ($result) {
            $player->sendMessage("ゲームから抜けました");
        } else {
            $player->sendMessage("ゲームから抜けることができませんでした");
        }
    }
}