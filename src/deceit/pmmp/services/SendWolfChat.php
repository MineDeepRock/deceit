<?php


namespace deceit\pmmp\services;


use deceit\dao\PlayerDataDAO;
use deceit\storages\GameStorage;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SendWolfChat
{
    static function execute(Player $sender, string $text): void {
        $senderData = PlayerDataDAO::findByName($sender->getName());
        if ($senderData === null) return;
        $game = GameStorage::findById($senderData->getBelongGameId());
        if ($game === null) return;

        if (!in_array($sender->getName(),$game->getWolfNameList())) return;

        foreach ($game->getWolfNameList() as $wolfName) {
            $wolfPlayer = Server::getInstance()->getPlayer($wolfName);
            if ($wolfPlayer === null) continue;
            if (!$wolfPlayer->isOnline()) continue;

            $wolfPlayer->sendMessage(TextFormat::RED . "人狼チャット:[{$sender->getName()}]" . $text);
        }
    }
}