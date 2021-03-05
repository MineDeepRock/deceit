<?php


namespace deceit\pmmp\services;


use deceit\models\Map;
use deceit\pmmp\blocks\ExitBlock;
use pocketmine\block\Block;
use pocketmine\Server;

class OpenExitPMMPService
{
    static function execute(Map $map): void {
        $exitVector = $map->getExitVector();
        $levelName = $map->getLevelName();
        $level = Server::getInstance()->getLevelByName($levelName);

        $level->setBlock($exitVector, Block::get(ExitBlock::ID));
    }
}