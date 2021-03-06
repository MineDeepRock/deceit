<?php


namespace deceit\pmmp;


use pocketmine\inventory\Inventory;
use pocketmine\Player;

class PlayerInventoryStorage
{
    /**
     * @var Inventory[]
     */
    static private array $inventories;

    static function save(Player $player): void {
        self::$inventories[$player->getName()] = $player->getInventory();
    }

    static function delete(Player $player): void {
        if(array_key_exists($player->getName(),self::$inventories)) {
            unset(self::$inventories[$player->getName()]);
        }
    }

    static function get(Player $player): ?Inventory {
        if(array_key_exists($player->getName(),self::$inventories)) {
            return self::$inventories[$player->getName()];
        }

        return null;
    }
}