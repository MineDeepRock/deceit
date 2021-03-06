<?php


namespace deceit\pmmp\utilities;


use deceit\DataFolderPath;
use pocketmine\Player;

class SavePlayerSkin
{
    static function execute(Player $player): void {
        $skin = $player->getSkin();
        file_put_contents(DataFolderPath::$playerSkin . $player->getName() . ".skin", $skin->getSkinData());
    }
}