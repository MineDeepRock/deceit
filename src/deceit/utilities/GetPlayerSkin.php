<?php


namespace deceit\utilities;


use deceit\DataFolderPath;
use pocketmine\entity\Skin;
use pocketmine\Player;

class GetPlayerSkin
{
    static function execute(Player $player): Skin {
        return new Skin("Standard_CustomSlim", file_get_contents(DataFolderPath::$playerSkin . $player->getName() . ".skin"));
    }
}