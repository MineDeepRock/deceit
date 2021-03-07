<?php


namespace deceit\pmmp\services;


use deceit\models\Game;
use deceit\pmmp\blocks\ExitBlock;
use pocketmine\block\Block;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class OpenExitPMMPService
{
    static function execute(Game $game): void {
        $map = $game->getMap();

        $exitVector = $map->getExitVector();
        $levelName = $map->getLevelName();
        $level = Server::getInstance()->getLevelByName($levelName);

        foreach ($game->getPlayerNameList() as $playerName) {
            $player = Server::getInstance()->getPlayer($playerName);
            if ($player===null) continue;
            if (!$player->isOnline()) continue;

            $player->sendMessage(TextFormat::GREEN . "出口が開きました");
        }

        $level->setBlock($exitVector, Block::get(ExitBlock::ID));
    }
}